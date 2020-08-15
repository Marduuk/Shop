<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class ErrorController
 * @package App\Controller
 */
class ErrorController implements NormalizerInterface
{
    /**
     * @param mixed $exception
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|string|null
     */
    public function normalize($exception, string $format = null, array $context = [])
    {
        if ($exception->getStatusCode() === 500)
            $msg = 'Something went wrong';
        elseif ($exception->getStatusCode() === 404)
            $msg = 'Resource not found';
        else
            $msg = $exception->getMessage();

        return [
            'success' => false,
            'exception'=> [
                'message' => $msg,
                'code' => $exception->getStatusCode(),
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof FlattenException;
    }
}