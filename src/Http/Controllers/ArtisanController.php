<?php
namespace SavanRathod\ArtisanCompass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class ArtisanController extends Controller
{
    public function index()
    {
        $allCommands = Artisan::all();

        $commands = [];

        foreach ($allCommands as $name => $command) {
            $definition = $command->getDefinition();

            $args = [];
            foreach ($definition->getArguments() as $argument) {
                $args[] = [
                    'name' => $argument->getName(),
                    'required' => $argument->isRequired(),
                    'description' => $argument->getDescription(),
                    'isArray' => $argument->isArray(),
                ];
            }

            $opts = [];
            foreach ($definition->getOptions() as $option) {
                $opts[] = [
                    'name' => $option->getName(),
                    'shortcut' => $option->getShortcut(),
                    'acceptValue' => $option->acceptValue(),
                    'isValueRequired' => $option->isValueRequired(),
                    'description' => $option->getDescription(),
                    'isArray' => $option->isArray(),
                ];
            }

            $commands[$name] = [
                'description' => $command->getDescription(),
                'arguments' => $args,
                'options' => $opts,
            ];
        }

        return view('artisan-compass::index', compact('commands'));
    }


    public function run(Request $request)
    {
        $output = new BufferedOutput;
        $command = $request->input('command');
        $parameters = $request->input('parameters', []);

        Artisan::call($command, $parameters, $output);

        return response()->json([
            'output' => $output->fetch()
        ]);
    }
}
