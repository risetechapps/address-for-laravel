<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Histórico de endereços
    |--------------------------------------------------------------------------
    */
    'history' => [
        /*
        | Tabela usada para VALIDAR o user_id antes de gravar no histórico.
        |
        | O AddressObserver grava quem alterou o endereço em address_histories.
        | Se essa coluna tem FK para uma tabela específica (ex.: `users`), gravar
        | um id de usuário que vive em OUTRA tabela (ex.: `authentications` em
        | apps multi-tenant) violaria a FK — por isso o id é validado antes.
        |
        | - 'users' (default): valida contra `users` (1 query por escrita de
        |   endereço). Se o id não existir lá, grava user_id = null.
        | - '<tabela>': valida contra a tabela informada (aponte para a tabela
        |   real do usuário autenticado, ex.: 'authentications').
        | - null: NÃO valida e NÃO grava user_id (zero query). Use quando o
        |   usuário não vive em nenhuma tabela com FK compatível.
        */
        'user_table' => env('ADDRESS_HISTORY_USER_TABLE', 'users'),
    ],

];
