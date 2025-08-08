<?php

namespace App\Livewire;

use App\Filament\Dashboard\Resources\SubscriptionResource;
use App\Services\PaymentProviders\PaymentService;
use App\Services\SubscriptionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class CancelSubscriptionForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public string $subscriptionUuid;

    private PaymentService $paymentService;

    private SubscriptionService $subscriptionService;

    public function boot(
        PaymentService $paymentService,
        SubscriptionService $subscriptionService,
    ) {
        $this->paymentService = $paymentService;
        $this->subscriptionService = $subscriptionService;
    }

    public function mount(string $subscriptionUuid): void
    {
        $this->subscriptionUuid = $subscriptionUuid;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        $reasons = [
            'too_expensive' => __('Too expensive'),
            'missing_features' => __('Missing features'),
            'found_another_software' => __('Found better product'),
            'other' => __('Other'),
        ];

        return $form
            ->schema([
                Select::make('reason')
                    ->options($reasons)
                    ->in(array_keys($reasons))
                    ->required()
                    ->nullable(false)
                    ->placeholder(__('Please tell us why you are canceling your subscription.')),
                Textarea::make('additional_information')
                    ->rows(5)
                    ->label(__('Additional information'))
                    ->helperText(__('Please tell us what we can do to improve our product.')),
            ])
            ->statePath('data');
    }

    public function cancel(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();

        $userSubscription = $this->subscriptionService->findActiveByUserAndSubscriptionUuid($user->id, $this->subscriptionUuid);

        if (! $userSubscription) {
            Notification::make()
                ->title(__('Error canceling subscription'))
                ->danger()
                ->send();

            $this->redirect(SubscriptionResource::getUrl());

            return;
        }

        $paymentProvider = $userSubscription->paymentProvider()->first();

        $paymentProviderStrategy = $this->paymentService->getPaymentProviderBySlug(
            $paymentProvider->slug
        );

        $this->subscriptionService->cancelSubscription(
            $userSubscription,
            $paymentProviderStrategy,
            $data['reason'],
            $data['additional_information'] ?? null,
        );

        Notification::make()
            ->title(__('Subscription will be cancelled at the end of the billing period.'))
            ->success()
            ->send();

        $this->redirect(SubscriptionResource::getUrl());
    }

    public function render()
    {
        return view('livewire.cancel-subscription-form', [
            'backUrl' => SubscriptionResource::getUrl(),
        ]);
    }
}
