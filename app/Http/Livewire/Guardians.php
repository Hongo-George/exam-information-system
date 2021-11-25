<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Guardian;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class Guardians extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $guardianId;
    public $userId;

    public $name;
    public $email;
    public $profession;
    public $location;

    public function render()
    {
        return view('livewire.guardians', [
            'guardians' => $this->getPaginatedGuardians()
        ]);
    }

    public function getPaginatedGuardians()
    {
        return Guardian::latest()->paginate(24);
    }

    public function rules()
    {
        return [
            'name' => ['bail', 'required', 'string'],
            'email' => ['bail', 'required', 'string', 'email', Rule::unique('users')->ignore($this->userId)],
            'profession' => ['bail', 'nullable'],
            'location' => ['bail', 'nullable']
        ];
    }

    public function addGuardian()
    {
        $data = $this->validate();

        try {

            DB::beginTransaction();

            /** @var Guardian */
            $guardian = Guardian::create($data);

            if($guardian){

                $user = $guardian->auth()->create(array_merge($data, [
                    'password' => Hash::make('password')
                ]));

                if($user){

                    DB::commit();

                    $this->reset(['name', 'email', 'profession', 'location']);

                    $this->resetPage();

                    session()->flash('status', 'A Guardian has successfully been added');

                    $this->emit('hide-upsert-guardian-modal');
                }

            }
            
        } catch (\Exception $exception) {
            
            DB::rollBack();

            Log::error($exception->getMessage(), [
                'guardian-id' => $this->guardianId,
                'action' => __CLASS__ . '@' . __METHOD__
            ]);

            session()->flash('error', 'A fatal error occurred check the logs');

            $this->emit('hide-upsert-guardian-modal');
        }
        
    }
}
