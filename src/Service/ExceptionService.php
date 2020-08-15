<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Form\Form;

/**
 * Class ExceptionService
 * @package App\Service
 */
class ExceptionService
{
    /**
     * @param Form $form
     * @return array
     */
    public function getFormErrors(Form $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            $errors[$form->getName()][] = $error->getMessage();
        }

        /** @var Form $child */
        foreach ($form as $child) {
            if (!$child->isValid()) {
                foreach ($child->getErrors() as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }

        return $errors;
    }
}