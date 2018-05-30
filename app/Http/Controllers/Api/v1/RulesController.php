<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Role;
use App\Models\Rule;
use Filter;
use Auth;
use League\Flysystem\Exception;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class RulesController
 *
 * @package App\Http\Controllers\Api\v1
 */
class RulesController extends ItemController
{
    /**
     * @return string
     */
    public function getItemClass(): string
    {
        return Rule::class;
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'role_id'        => 'required',
            'object'         => 'required',
            'action'         => 'required',
            'allow'          => 'required',
        ];
    }

    /**
     * @return string
     */
    public function getEventUniqueNamePart(): string
    {
        return 'rule';
    }

    /**
     * @api {post} /api/v1/rules/edit Edit
     * @apiDescription Edit Rule
     * @apiVersion 0.1.0
     * @apiName EditRule
     * @apiGroup Rule
     *
     * @apiParam {Integer} role_id Rule's Role's ID
     * @apiParam {String}  object  Object name
     * @apiParam {String}  action  Action name

     * @apiSuccess {String} message OK
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function edit(Request $request): JsonResponse
    {
        $requestData = Filter::process($this->getEventUniqueName('request.item.edit'), $request->all());
        $cls = $this->getItemClass();
        Role::updateRules();

        $validator = Validator::make(
            $requestData,
            Filter::process($this->getEventUniqueName('validation.item.edit'), $this->getValidationRules())
        );

        if ($validator->fails()) {
            return response()->json(
                Filter::process($this->getEventUniqueName('answer.error.item.edit'), [
                    'error' => 'validation fail',
                    'reason' => $validator->errors()
                ]),
                400
            );
        }

        try {
            Role::updateAllow($requestData['role_id'], $requestData['object'], $requestData['action'], $requestData['allow']);
        } catch (\Exception $e) {
            return response()->json(Filter::process(
                $this->getEventUniqueName('answer.error.item.edit'), [
                    'message' => $e->getMessage(),
                ]),
                $e->getCode()
            );
        };

        return response()->json(Filter::process(
            $this->getEventUniqueName('answer.success.item.edit'), [
                'message' => 'OK',
            ]
        ));
    }

    /**
     * @api {post} /api/v1/rules/bulk/edit bulkEdit
     * @apiDescription Editing Multiple Rules
     * @apiVersion 0.1.0
     * @apiName bulkEditRules
     * @apiGroup Rule
     *
     * @apiParam {Object[]} rules                Array of objects Rule
     * @apiParam {Object}   rules.object         Rule object
     * @apiParam {Integer}  rules.object.role_id Rule's Role's ID
     * @apiParam {String}   rules.object.object  Object name
     * @apiParam {String}   rules.object.action  Action name
     *
     * @apiSuccess {String[]}  messages         Array of string response
     * @apiSuccess {String}    messages.message  OK
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bulkEdit(Request $request): JsonResponse
    {
        $requestData = Filter::process($this->getEventUniqueName('request.item.bulkEdit'), $request->all());
        $result = [];
        Role::updateRules();

        if (empty($requestData['rules'])) {
            return response()->json(Filter::process(
                $this->getEventUniqueName('answer.error.item.bulkEdit'), [
                    'error' => 'validation fail',
                    'reason' => 'rules is empty'
                ]),
                400
            );
        }

        foreach ($requestData['rules'] as $rule) {
            $validator = Validator::make(
                $rule,
                Filter::process($this->getEventUniqueName('validation.item.edit'), $this->getValidationRules())
            );

            if ($validator->fails()) {
                $result[] = [
                    'error' => 'validation fail',
                    'reason' => $validator->errors(),
                    'code' => 400
                ];
                continue;
            }

            try {
                if (Role::updateAllow($rule['role_id'], $rule['object'], $rule['action'], $rule['allow'])) {
                    $result[] = ['message' => 'OK'];
                };
            } catch (\Exception $e) {
                $result[] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            };
        }

        return response()->json(Filter::process(
            $this->getEventUniqueName('answer.success.item.bulkEdit'), [
                'messages' => $result,
            ]
        ));
    }
}
