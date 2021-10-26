<?php

namespace Espo\Modules\Esignature\Controllers;

use Espo\Core\{
Container,
Exceptions\BadRequest,
Api\Request
};

class Esignature
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function postActionPrintForEsignature(Request $request): string
    {
        $data = $request->getParsedBody();
        $entity = $this->container->get('entityManager')->getEntity($data->entityType,$data->entityId);
        $template = $this->container->get('entityManager')->getEntity('Template',$data->templateId);
        if(empty($entity)) {
            throw new BadRequest('Entity "{$data->entityType}" ID "{$data->entityId}" not found');
        }
        if(empty($template)) {
            throw new BadRequest('Template ID "{$data->templateId}" not found');
        }
        $result = $this->container->get('serviceFactory')->create('Esignature')->printForEsignature($entity, $template, $data->isPortal);
        return $result;
    }

}