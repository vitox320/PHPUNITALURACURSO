<?php

namespace Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorEMail;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


class EncerradorTest extends TestCase
{

    private $encerrador;
    private $lelilaoFiat147;
    private $leilaoVariant;
    /**
     * @var EnviadorEMail|MockObject
     */
    private $enviadorEmail;

    protected function setUp(): void
    {
        $this->lelilaoFiat147 = new Leilao(
            'Fiat 147 0km',
            new \DateTimeImmutable('8 days ago')
        );

        $this->leilaoVariant = new Leilao('Variant 1972 0km',
            new \DateTimeImmutable('10 days ago')
        );

        //$leilaoDao = $this->createMock(LeilaoDao::class);
        $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
            ->setConstructorArgs([new \PDO('sqlite::memory:')])
            ->getMock();
        $leilaoDao->method('recuperarNaoFinalizados')->willReturn([$this->lelilaoFiat147, $this->leilaoVariant]);
        $leilaoDao->method('recuperarFinalizados')->willReturn([$this->lelilaoFiat147, $this->leilaoVariant]);
        $leilaoDao->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive(
                [$this->lelilaoFiat147],
                [$this->leilaoVariant]
            );
        $this->enviadorEmail = $this->createMock(EnviadorEMail::class);
        $this->encerrador = new Encerrador($leilaoDao, $this->enviadorEmail);
    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {

        $this->encerrador->encerra();


        $leiloes = [$this->lelilaoFiat147, $this->leilaoVariant];

        self::assertCount(2, $leiloes);
        self::assertTrue($leiloes[0]->estaFinalizado());
        self::assertTrue($leiloes[1]->estaFinalizado());
    }

    public function testDeveContinuarOProcessamentoAoEncontrarErroAoEnviarEmail()
    {
        $e = new \DomainException('Erro ao enviar email');
        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willThrowException($e);

        $this->encerrador->encerra();
    }

    public function testeSoDeveEnviarLeilaoPorEmailAposFinalizado()
    {
        $this->enviadorEmail->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willReturnCallback(function (Leilao $leilao) {
                static::assertTrue($leilao->estaFinalizado());
            });

        $this->encerrador->encerra();
    }
}