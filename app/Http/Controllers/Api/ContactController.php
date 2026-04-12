<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use App\Http\Resources\Api\ContactResource;
use App\Http\Requests\Api\ContactStoreRequest;
use App\Services\SystemNotificationService;

class ContactController extends ApiController
{
    public function __construct()
    {
        $this->model = Contact::class;
        $this->resource = ContactResource::class;
    }

    public function store(ContactStoreRequest $request)
    {
        $item = $this->model::create([
            ...$request->validated(),
            'source' => 'contact_form',
        ]);

        app(SystemNotificationService::class)->notifyAdmins(
            __('New Contact Message'),
            __('A new contact message has been received from :name.', ['name' => $item->name]),
            'warning',
            ['source' => 'contact']
        );

        return new $this->resource($item);
    }
}
