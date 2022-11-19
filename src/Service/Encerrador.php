<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;

class Encerrador
{
    /**
     * @var LeilaoDao
     */
    private $dao;
    /**
     * @var EnviadorEMail
     */
    private $enviadorEMail;

    public function __construct(LeilaoDao $dao, EnviadorEMail $enviadorEMail)
    {
        $this->dao = $dao;
        $this->enviadorEMail = $enviadorEMail;
    }

    public function encerra()
    {

        $leiloes = $this->dao->recuperarNaoFinalizados();

        foreach ($leiloes as $leilao) {
            if ($leilao->temMaisDeUmaSemana()) {
                try {
                    $leilao->finaliza();
                    $this->dao->atualiza($leilao);
                    $this->enviadorEMail->notificarTerminoLeilao($leilao);
                } catch (\DomainException $e) {
                    error_log($e->getMessage());
                }

            }
        }
    }
}
