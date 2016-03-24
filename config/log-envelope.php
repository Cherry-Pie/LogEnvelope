<?php

return [

    /*
     * Log emails receiver email.
     */
    'email_to' => '',
    
    /*
     * From email.
     * 
     * (default value: log-envelop@your-domain.com)
     */
    'email_from' => null,

    /*
     * Decide wether it should queue
     *
     */
    'should_queue' => true,

    /*
     * Decide where to log to
     *
     * Options: mail, database
     */
    'log_to' => 'mail',

    /*
     * The name of the sender.
     * 
     * (default value: Log Envelope)
     */

    'email_from_name' => '',

    
    /*
     * How many lines to show near exception line.
     */
    'lines_count' => 12,
    
    /*
     * List of exceptions to skip sending.
     */
    'except' => [
        //'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
    ],
    
];
