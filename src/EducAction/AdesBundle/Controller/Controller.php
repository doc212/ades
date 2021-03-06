<?php
/**
 * Copyright (c) 2014 Tuan-Tu TRAN
 * 
 * This file is part of ADES.
 * 
 * ADES is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ADES is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ADES.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace EducAction\AdesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as SfController;
use Symfony\Component\HttpFoundation\Session\Session;
use EducAction\AdesBundle\Bag;
use EducAction\AdesBundle\FlashBagWrapper;

class Controller extends SfController
{
    protected $params;
    private $flashWrapper = NULL;

    public function __construct()
    {
        if(method_exists(get_parent_class(),"__construct")){
            parent::__construct();
        }
        $this->params=new Bag();
    }

    protected function View($template)
    {
        if(strpos($template,":") === FALSE){
            $controller=preg_replace("/^EducAction\\\\AdesBundle\\\\Controller\\\\(.+)Controller$/", "$1", get_class($this));
            $template="EducActionAdesBundle:$controller:$template";
        }
        $allParameters=array();
        $givenParameters=array_merge(array($this->params), array_slice(func_get_args(), 1));
        foreach ($givenParameters as $parameters) {
            if ($parameters) {
                if (!is_array($parameters)) {
                    if(is_a($parameters, "EducAction\\AdesBundle\\Bag")){
                        $parameters=get_object_vars($parameters);
                    } else {
                        throw new \Exception("View parameter cannot be a ".get_class($parameters));
                    }
                }
                $allParameters=array_replace($allParameters, $parameters);
            }
        }
        return $this->Render($template, $allParameters);
	}

    public function flash()
    {
        if(!$this->flashWrapper) {
            $this->flashWrapper = new FlashBagWrapper($this->getRequest()->getSession()->getFlashBag());
        }
        return $this->flashWrapper;
    }

    public function getUser()
    {
        return $this->get("educ_action.ades.user");
    }

    public function getSecret()
    {
        return $this->container->getParameter("secret");
    }

    /**
     * Default implementation for IProtected
     */
    public function isPublicAction($action)
    {
        return FALSE;
    }

    protected function redirectLogin()
    {
        return $this->redirectRoute("educ_action_ades_login");
    }

    protected function redirectRoute($route)
    {
        return $this->redirect($this->generateUrl($route));
    }

    /**
     * Throw a not found exception created with $this->createNotFoundException.
     *
     * This is useful in construcs like DoSomething or $this->ThrowNotFoundException($msg).
     *
     * @param string $msg the exception message.
     * @return void
     */
    protected function ThrowNotFoundException($msg)
    {
        throw $this->createNotFoundException($msg);
    }

    /**
     * Throw an Exception with given message.
     *
     * This is useful for constructs like DoSomething or $this->throwException($msg)
     *
     * @param string $msg the exception message.
     * @return void
     */
    protected function throwException($msg)
    {
        throw new \Exception($msg);
    }
}

