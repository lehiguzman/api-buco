<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Gestion de PushTokens
 *
 * @author Strapp International Inc.
 */
class GestionPushTokens
{
    protected $em;

    function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Obtener Push Tokens del Usuario
     */
    public function usuarioPushTokens($userID)
    {
        $tokens = $this->em->getRepository("App:User")->findUserPushTokens($userID);

        $resuls = [];
        foreach ($tokens as $key => $value) {
            if (strlen($value->getPushToken()) > 20) {
                $item['userID'] = $value->getUser()->getId();
                $item['token'] = $value->getPushToken();
                $usuario = $this->em->getRepository("App:User")->find($item['userID']);
                if (in_array('ROLE_PROFESIONAL', $usuario->getRoles())) {
                    $profesional = $this->em->getRepository("App:Profesional")->findOneByUser([$value->getUser()]);
                    $item['profesionalID'] = $profesional->getId();
                }
                $resuls[] = $item;
            }
        }

        return $resuls;
    }
}
