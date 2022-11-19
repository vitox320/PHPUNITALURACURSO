<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EnviadorEMail
{
    public function notificarTerminoLeilao(Leilao $leilao)
    {
        $sucesso = mail(
            'usuario@email.com',
            'Leilao finalizado',
            'O leilao para' . $leilao->recuperarDescricao() . 'foi finalizado');

        if (!$sucesso) {
            throw new \DomainException('Erro ao enviar email');
        }
    }
}