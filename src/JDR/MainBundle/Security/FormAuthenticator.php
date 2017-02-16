<?php

namespace JDR\MainBundle\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityManager;

class FormAuthenticator extends AbstractGuardAuthenticator
{
    private $em;

    /**
    * @var \Symfony\Component\Routing\RouterInterface
    */
    private $router;

    /**
    * Default message for authentication failure.
    *
    * @var string
    */
    private $failMessage = 'Identifiants incorrects';

    /**
    * Creates a new instance of FormAuthenticator
    */
    public function __construct(EntityManager $em, RouterInterface $router) {
        $this->router = $router;
        $this->em = $em;
    }

    /**
    * {@inheritdoc}
    */
    public function getCredentials(Request $request)
    {
        if ($request->getPathInfo() != '/login' || !$request->isMethod('POST')) {
          return;
        }

        return array(
          'email' => $request->request->get('_email'),
          'password' => $request->request->get('_password'),
        );
    }

    /**
    * {@inheritdoc}
    */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $email = $credentials['email'];
        if (substr($email, 0, 1) == '@')
        {
            throw new CustomUserMessageAuthenticationException('Votre adresse e-mail ne peut pas commencer par un @');
        }

        try {
            return ($this->em->getRepository('JDRUserBundle:User')->findOneByEmail($email));
        }
        catch (UsernameNotFoundException $e) {
            throw new CustomUserMessageAuthenticationException($this->failMessage);
        }
    }

    /**
    * {@inheritdoc}
    */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $encoder_service = $this->get('security.encoder_factory');
        $encoder = $encoder_service->getEncoder($user);
        
        
        if (password_verify($user->getPassword())) {
          return true;
        }
        throw new CustomUserMessageAuthenticationException($this->failMessage);
    }

    /**
    * {@inheritdoc}
    */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $url = $this->router->generate('admin');
        return new RedirectResponse($url);
    }

    /**
    * {@inheritdoc}
    */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        $url = $this->router->generate('login');
        return new RedirectResponse($url);
    }

    /**
    * {@inheritdoc}
    */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url = $this->router->generate('login');
        return new RedirectResponse($url);
    }

    /**
    * {@inheritdoc}
    */
    public function supportsRememberMe()
    {
        return false;
    }
}