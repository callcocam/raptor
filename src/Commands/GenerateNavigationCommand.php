<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Raptor\Commands;

use Callcocam\Raptor\Services\RaptorNavigationGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Comando para gerar a navegação do Raptor
 */
class GenerateNavigationCommand extends Command
{
    /**
     * Assinatura do comando
     *
     * @var string
     */
    protected $signature = 'raptor:navigation 
                            {--fresh : Gerar sem usar o cache}
                            {--output= : Caminho para salvar o JSON de navegação}';

    /**
     * Descrição do comando
     *
     * @var string
     */
    protected $description = 'Gera a estrutura de navegação do Raptor a partir dos controladores';

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
        $useFresh = $this->option('fresh');
        $outputPath = $this->option('output');

        $this->info('Gerando estrutura de navegação do Raptor...');

        $navigation = $this->generator->generate(!$useFresh);

        $this->info('Navegação gerada com sucesso!');
        $this->info('Total de itens: ' . $navigation->count());

        // Se um caminho de saída foi especificado, salvar como JSON
        if ($outputPath) {
            $json = $this->generator->renderJson(!$useFresh);
            File::put($outputPath, $json);
            $this->info("Navegação salva em: {$outputPath}");
        }

        // Exibir a estrutura de navegação
        $this->displayNavigation($navigation);

        return Command::SUCCESS;
    }

    /**
     * Exibe a estrutura de navegação no terminal
     *
     * @param \Illuminate\Support\Collection $navigation
     * @param int $level
     * @return void
     */
    protected function displayNavigation($navigation, $level = 0)
    {
        $indent = str_repeat('  ', $level);

        foreach ($navigation as $item) {
            if (isset($item['isGroup']) && $item['isGroup']) {
                $this->line("{$indent}📁 <fg=yellow>{$item['label']}</> (Grupo)");
                $this->displayNavigation($item['items'], $level + 1);
            } else {
                $icon = isset($item['icon']) ? "({$item['icon']})" : '';
                $this->line("{$indent}🔗 <fg=green>{$item['label']}</> {$icon} → {$item['url']}");
            }
        }
    }
}
 
