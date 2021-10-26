<?php

namespace Espo\Modules\Esignature\Controllers;

use Espo\Core\{
Di,
Api\Request
};

class Esignature implements Di\ServiceFactoryAware, Di\EntityManagerAware
{

    use Di\ServiceFactorySetter;

    use Di\EntityManagerSetter;

    public function postActionPrintForEsignature(Request $request): string
    {
        $data = $request->getParsedBody();
        $entity = $this->entityManager->getEntity($data->entityType,$data->entityId);
        $template = $this->entityManager->getEntity('Template',$data->templateId);
        if(empty($entity)) {
            throw new BadRequest('Entity "{$data->entityType}" ID "{$data->entityId}" not found');
        }
        if(empty($template)) {
            throw new BadRequest('Template ID "{$data->templateId}" not found');
        }
        $result = $this->serviceFactory->create('Esignature')->printForEsignature($entity, $template, $data->isPortal);
        return $result;
    }

}