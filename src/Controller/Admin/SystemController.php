<?php

namespace Starfruit\HelperBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * @Route("/system")
 */
class SystemController extends BaseController
{
    /**
     * @Route("/phpinfo", methods={"GET"})
     *
     * @param Request $request
     * @param Profiler $profiler
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function phpinfoAction(Request $request, ?Profiler $profiler)
    {
        if ($profiler) {
            $profiler->disable();
        }

        ob_start();
        phpinfo();
        $content = ob_get_clean();

        return new Response($content);
    }
}
