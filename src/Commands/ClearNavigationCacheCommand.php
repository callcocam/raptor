<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Commands;

use Callcocam\Raptor\Services\RaptorNavigationGenerator;
use Illuminate\Console\Command;


/**
 * Comando para limpar o cache de navegação do Raptor
 */
class ClearNavigationCacheCommand extends Command
{
    /**
     * Assinatura do comando
     *
     * @var string
     */
    protected $signature = 'raptor:navigation:clear';

    /**
     * Descrição do comando
     *
     * @var string
     */
    protected $description = 'Limpa o cache de navegação do Raptor';

    /**
     * Gerador de navegação
     *
     * @var RaptorNavigationGenerator
     */
    protected RaptorNavigationGenerator $generator;

    /**
     * Construtor
     *
     * @param RaptorNavigationGenerator $generator
     */
    public function __construct(RaptorNavigationGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    /**
     * Executa o comando
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Limpando cache de navegação do Raptor...');

        if ($this->generator->clearCache()) {
            $this->info('Cache de navegação limpo com sucesso!');
        } else {
            $this->warn('Não foi possível limpar o cache ou não havia cache para limpar.');
        }

        return Command::SUCCESS;
    }
}
