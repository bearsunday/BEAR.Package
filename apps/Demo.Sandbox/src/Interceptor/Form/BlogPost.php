<?php

namespace Demo\Sandbox\Interceptor\Form;

use BEAR\Sunday\Inject\NamedArgsInject;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;

/**
 * Post form
 */
class BlogPost implements MethodInterceptor
{
    use NamedArgsInject;

    /**
     * Error
     *
     * @var array
     */
    private $errors = [
        'title' => '',
        'body' => ''
    ];

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        // retrieve page and query
        $args = $this->namedArgs->get($invocation);
        $page = $invocation->getThis();

        // strip tags
        foreach ($args as &$arg) {
            strip_tags($arg);
        }

        // required title
        if ($args['title'] === '') {
            $this->errors['title'] = 'title required.';
        }

        // required body
        if ($args['body'] === '') {
            $this->errors['body'] = 'body required.';
        }

        // valid form ?
        if (implode('', $this->errors) === '') {
            return $invocation->proceed();
        }

        // error, modify 'GET' page wih error message.
        $page['errors'] = $this->errors;
        $page['submit'] = [
            'title' => $args['title'],
            'body' => $args['body']
        ];

        return $page->onGet();
    }
}
