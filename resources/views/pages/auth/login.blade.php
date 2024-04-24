<?php

use App\Models\User;
use Illuminate\Auth\Events\Login;
use function Laravel\Folio\{middleware, name};
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

middleware(['guest']);
name('auth.login');

new class extends Component
{
    #[Validate('required|email')]
    public $email = '';

    #[Validate('required')]
    public $password = '';

    public $showPasswordField = false;

    public $authData = [];
    public $customizations = [];

    public function mount(){
        $this->authData = config('devdojo.auth.pages.login');
        $this->customizations = config('devdojo.auth.customizations');
    }

    public function editIdentity(){
        $this->showPasswordField = false;
    }

    public function authenticate()
    {
        if(!$this->showPasswordField){
            $this->validateOnly('email');
            $this->showPasswordField = true;
            $this->js("setTimeout(function(){ window.dispatchEvent(new CustomEvent('focus-password', {})); }, 10);");
            return;
        }
        
        $this->validate();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->addError('email', trans('auth.failed'));

            return;
        }

        event(new Login(auth()->guard('web'), User::where('email', $this->email)->first(), true));

        return redirect()->intended('/');
    }
};

?>

<x-auth::layouts.app>
    @volt('auth.login') 
        <x-auth::elements.container>
        
                <x-auth::elements.heading 
                :text="($customizations['login']['text']['headline'] ?? 'No Heading')" 
                :align="($customizations['heading']['align'] ?? 'center')" 
                :description="($customizations['login']['text']['subheadline'] ?? 'No Description')"
                :show_subheadline="($customizations['login']['show_subheadline'] ?? false)" />
                
                <form wire:submit="authenticate" class="mt-5 space-y-5">

                    @if($showPasswordField)
                        <x-auth::elements.input-placeholder value="{{ $email }}">
                            <button type="button" wire:click="editIdentity" class="font-medium text-blue-500">Edit</button>
                        </x-auth::elements.input-placeholder>
                    @else  
                        <x-auth::elements.input label="Email Address" type="email" wire:model="email" autofocus="true" id="email" />
                    @endif
                    
                    @if($showPasswordField)
                        <x-auth::elements.input label="Password" type="password" wire:model="password" id="password" />
                        <div class="flex justify-between items-center mt-6 text-sm leading-5">
                            <x-auth::elements.text-link href="{{ route('auth.password.request') }}">Forgot your password?</x-auth::elements.text-link>
                        </div>
                    @endif

                    <x-auth::elements.button type="primary" rounded="md" size="md" submit="true">Continue</x-auth::elements.button>
                </form>
                
                
                <div class="mt-3 space-x-0.5 text-sm leading-5 text-left text-gray-400 dark:text-gray-300">
                    <span>Don't have an account?</span>
                    <x-auth::elements.text-link href="{{ route('auth.register') }}">Sign up</x-auth::elements.text-link>
                </div>

        </x-auth::elements.container>
    @endvolt
</x-auth::layouts.app>