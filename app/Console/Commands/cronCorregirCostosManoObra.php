<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class cronCorregirCostosManoObra extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:corregir_costos_mano_obra';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('comando:dev', [
            'comando' => 'corregir_costos_mano_obra',
            'desde' => getSemanaByDate(opDiasFecha('-', 49, hoy()))->codigo,
            'hasta' => getSemanaByDate(hoy())->codigo,
        ]);
    }
}