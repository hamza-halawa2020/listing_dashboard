<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use App\Http\Resources\Api\ContactResource;
use App\Http\Requests\Api\ContactStoreRequest;

class ContactController extends ApiController
{
    public function __construct()
    {
        $this->model = Contact::class;
        $this->resource = ContactResource::class;
    }

    public function store(ContactStoreRequest $request)
    {
        $item = $this->model::create($request->validated());

        return new $this->resource($item);
    }
}
