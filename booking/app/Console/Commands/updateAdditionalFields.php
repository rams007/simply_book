<?php

namespace App\Console\Commands;

use App\Helpers\JsonRpcClient;
use App\Models\AdditionalFields;
use Illuminate\Console\Command;

class updateAdditionalFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateAdditionalFields';

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
        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $token = $loginClient->getToken(env('COMPANY_LOGIN'), env('API_KEY'));
        $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-Token: ' . $token
            )
        ));

        $services = $client->getEventList();
        foreach ($services as $service) {
            if ($service->is_active === "0") {
                continue;
            }

            $serviceId = $service->id;

            $allAdditionalFields = $client->getAdditionalFields($serviceId);

            foreach ($allAdditionalFields as $field) {

                $record = AdditionalFields::where('event_id', $serviceId)->where('field_id', $field->id)->first();
                if (!$record) {
                    AdditionalFields::create([
                        'event_id' => $serviceId,
                        'field_id' => $field->id,
                        'name' => $field->name,
                        'title' => $field->title,
                        'type' => $field->type,
                        'length' => $field->length,
                        'values' => $field->values,
                        'default' => $field->default,
                        'is_null' => $field->is_null,
                        'is_visible' => $field->is_visible,
                        'pos' => $field->pos,
                        'show_for_all_events' => $field->show_for_all_events,
                        'value' => $field->value,
                        'plugin_event_field_value_id' => $field->plugin_event_field_value_id,
                    ]);


                } else {

                    $record->name = $field->name;
                    $record->title = $field->title;
                    $record->type = $field->type;
                    $record->length = $field->length;
                    $record->values = $field->values;
                    $record->default = $field->default;
                    $record->is_null = $field->is_null;
                    $record->is_visible = $field->is_visible;
                    $record->pos = $field->pos;
                    $record->show_for_all_events = $field->show_for_all_events;
                    $record->value = $field->value;
                    $record->plugin_event_field_value_id = $field->plugin_event_field_value_id;
                    $record->save();
                }

            }
        }
    }
}
