<?php

namespace App\Responsable;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ResponseSuccess implements Responsable
{
    public function __construct(protected mixed $data = [], protected string $message = 'Success', protected int $statusCode = Response::HTTP_OK)
    {

    }

    public function toResponse($request)
    {
        $response['data'] = $this->data instanceof AnonymousResourceCollection ? $this->data->response()->getData() : $this->data;
        $response['message'] = $this->message;

        return response()->json($response, $this->statusCode);
    }

}
