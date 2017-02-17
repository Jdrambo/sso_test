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
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Doctrine\ORM\EntityManager;

class FormAuthenticator extends AbstractGuardAuthenticator
{
    /**
    *   @var \Doctrine\ORM\EntityManager
    */
    private $em;

    /**
    * @var \Symfony\Component\Security\Core\Encoder\EncoderFactory
    */
    private $encoder_service;

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
    public function __construct(EntityManager $em, RouterInterface $router, EncoderFactory $encoder_service) {
        $this->router = $router;
        $this->em = $em;
        $this->encoder_service = $encoder_service;
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
        $user = $this->em->getRepository('JDRUserBundle:User')->findOneByEmail($email);
        if ($user)
            return ($user);
        throw new CustomUserMessageAuthenticationException('Identifiants incorrects');
    }

    /**
    * {@inheritdoc}
    */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $encoder = $this->encoder_service->getEncoder($user);
        
        if ($encoder->isPasswordValid($user->getPassword(), $credentials['password'], $user->getSalt())) {
          return true;
        }
        throw new CustomUserMessageAuthenticationException('Identifiants incorrects');
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