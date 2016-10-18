<?php

return [
    
    'User' => App\User::class,
    
    'Routes' => [
        'getMessages' => 'chat',
        'sendMessage' => 'chat/send',
        'deleteMessage' => 'chat/destroy',
    ],
    
];