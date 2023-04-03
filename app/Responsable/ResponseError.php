<?php

namespace App\Responsable;

use Symfony\Component\HttpFoundation\Response;

class ResponseError implements \Illuminate\Contracts\Support\Responsable
{
    public function __construct(protected ?string $message = null, protected \Throwable|null $ex = null, protected int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
    }

    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        $data['message'] = $this->message ?? $this->ex->getMessage();
        if ($this->ex && config('app.debug')) {
            $data['debug'] = [
                'file' => $this->ex->getFile(),
                'line' => $this->ex->getLine(),
                'message' => $this->ex->getMessage(),
            ];
        }

        return response()->json($data, $this->statusCode);
    }
}
