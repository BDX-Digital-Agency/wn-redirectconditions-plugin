<?php

declare(strict_types=1);

namespace Bdx\RedirectConditions;

use Backend\Classes\FormTabs;
use Backend\Widgets\Form;
use Bdx\RedirectConditions\Models\ConditionParameter;
use CreativeSizzle\Redirect\Classes\Contracts\RedirectConditionInterface;
use CreativeSizzle\Redirect\Classes\Contracts\RedirectManagerInterface;
use CreativeSizzle\Redirect\Controllers\Redirects;
use CreativeSizzle\Redirect\Models\Redirect;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Event;
use System\Classes\PluginBase;
use Winter\Storm\Foundation\Application;

final class Plugin extends PluginBase
{
    public $require = [
        'CreativeSizzle.Redirect',
    ];

    public function register(): void
    {
        Redirect::extend(static function (Redirect $redirect): void {
            $redirect->hasMany['conditionParameters'] = [
                0 => ConditionParameter::class,
                'table' => 'bdx_redirectconditions_condition_parameters',
            ];

            $redirect->bindEvent('model.afterSave', static function () use ($redirect): void {
                /** @var Dispatcher $dispatcher */
                $dispatcher = resolve(Dispatcher::class);
                $dispatcher->dispatch('bdx.redirect.afterRedirectSave', ['redirect' => $redirect]);
            });
        });

        Event::listen('bdx.redirect.afterRedirectSave', static function (Redirect $redirect): void {
            if (! Application::getInstance()->runningInBackend()) {
                return;
            }

            /** @var RedirectManagerInterface $manager */
            $manager = resolve(RedirectManagerInterface::class);

            $postData = request()->get('Redirect', []);

            /** @var string $condition */
            foreach ($manager->getConditions() as $condition) {
                /** @var RedirectConditionInterface $condition */
                $condition = resolve($condition);

                $formValues = array_get(
                    $postData,
                    sprintf('_BdxRedirectConditionParameters.%s', $condition->getCode()),
                    []
                );

                $isEnabled = (bool) array_get(
                    $postData,
                    sprintf('_BdxRedirectConditionEnabled.%s', $condition->getCode()),
                    false
                );

                ConditionParameter::query()->updateOrCreate(
                    [
                        'redirect_id' => $redirect->getKey(),
                        'condition_code' => $condition->getCode(),
                    ],
                    [
                        'is_enabled' => $isEnabled ? date('Y-m-d H:i:s') : null,
                        'parameters' => $formValues,
                    ]
                );
            }
        });

        Event::listen('backend.form.extendFields', static function (Form $form): void {
            if (! $form->getController() instanceof Redirects) {
                return;
            }

            if (! ($form->model instanceof Redirect)) {
                return;
            }

            /** @var RedirectManagerInterface $manager */
            $manager = resolve(RedirectManagerInterface::class);

            foreach ($manager->getConditions() as $condition) {
                /** @var RedirectConditionInterface $condition */
                $condition = resolve($condition);

                $formParentFieldKey = sprintf('_BdxRedirectConditionEnabled[%s]', $condition->getCode());

                $form->addFields([
                    $formParentFieldKey => [
                        'label' => $condition->getDescription(),
                        'tab' => RedirectConditionInterface::TAB_NAME,
                        'type' => 'checkbox',
                        'comment' => $condition->getExplanation(),
                    ],
                ], FormTabs::SECTION_PRIMARY);

                foreach ($condition->getFormConfig() as $formFieldKey => $formField) {
                    $formFieldKey = sprintf(
                        '_BdxRedirectConditionParameters[%s][%s]',
                        $condition->getCode(),
                        $formFieldKey
                    );

                    $formField['trigger'] = [
                        'action' => 'show',
                        'field' => $formParentFieldKey,
                        'condition' => 'checked',
                    ];

                    $formField['cssClass'] = 'field-indent';

                    $form->addFields([
                        $formFieldKey => $formField,
                    ], FormTabs::SECTION_PRIMARY);
                }
            }

            if (! $form->model->exists) {
                return;
            }

            /*
             * Populate the form with the stored values.
             */

            $data = [];

            /** @var ConditionParameter $conditionParameter */
            foreach ($form->model->getAttribute('conditionParameters') as $conditionParameter) {
                $data['_BdxRedirectConditionParameters'][$conditionParameter->getAttribute('condition_code')]
                    = $conditionParameter->getAttribute('parameters');
                $data['_BdxRedirectConditionEnabled'][$conditionParameter->getAttribute('condition_code')]
                    = (bool) $conditionParameter->getAttribute('is_enabled');
            }

            $form->setFormValues($data);
        });
    }
}
