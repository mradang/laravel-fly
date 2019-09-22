<?php

namespace mradang\LaravelFly\Traits;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait CustomFieldControllerTrait {

    abstract protected function customFieldModel();

    protected function customFieldExcludeGroups () {
        return ['默认分组', '保留字段'];
    }
    protected function customFieldExcludeFields () {
        return [];
    }

    public function saveFieldGroup(Request $request) {
        $validatedData = $request->validate([
            'id' => 'required|integer',
            'name' => [
                'required',
                'string',
                'not_in:'.implode(',', $this->customFieldExcludeGroups()),
                'name' => Rule::unique('custom_field_groups')->where(function ($query) {
                    $query->where('model', $this->customFieldModel());
                })->ignore($request->input('id')),
            ],
        ], [
            'name.not_in' => '分组名无效',
            'name.required' => '分组名必填',
            'name.unique' => '分组名已存在',
        ]);

        extract($validatedData);

        if ($id) {
            return $this->customFieldModel()::customFieldGroupUpdate($id, $name);
        } else {
            return $this->customFieldModel()::customFieldGroupCreate($name);
        }
    }

    public function getFieldGroups() {
        return $this->customFieldModel()::customFieldGroups();
    }

    public function deleteFieldGroup(Request $request) {
        $validatedData = $request->validate([
            'id' => 'required|integer',
        ]);

        extract($validatedData);

        try {
            $this->customFieldModel()::customFieldGroupDelete($id);
        } catch (\Exception $e) {
            return response($e->getMessage(), 400);
        }
    }

    public function sortFieldGroups(Request $request) {
        $validatedData = $request->validate([
            '*.id' => 'required|integer|min:1',
            '*.sort' => 'required|integer',
        ]);
        $this->customFieldModel()::customFieldGroupSaveSort($validatedData);
    }

    public function saveField(Request $request) {
        $validatedData = $request->validate([
            'id' => 'required|integer',
            'name' => [
                'required',
                'string',
                'not_in:'.implode(',', $this->customFieldExcludeFields()),
                Rule::unique('custom_fields')->where(function ($query) use ($request) {
                    $query->where([
                        'model' => $this->customFieldModel(),
                        'group_id' => $request->input('group_id'),
                    ]);
                })->ignore($request->input('id')),
            ],
            'type' => 'required|integer|min:1',
            'options' => 'nullable|array',
            'group_id' => 'required|integer',
            'required' => 'boolean',
        ], [
            'name.unique' => '字段名已存在',
        ]);

        extract($validatedData);
        if (!isset($required)) {
            $required = false;
        }

        if ($id) {
            return $this->customFieldModel()::customFieldUpdate($id, $name, $type, $options, $group_id, $required);
        } else {
            return $this->customFieldModel()::customFieldCreate($name, $type, $options, $group_id, $required);
        }
    }

    public function getFields() {
        return $this->customFieldModel()::customFields();
    }

    public function deleteField(Request $request) {
        $validatedData = $request->validate([
            'id' => 'required|integer',
        ]);

        extract($validatedData);

        $this->customFieldModel()::customFieldDelete($id);
    }

    public function sortFields(Request $request) {
        $validatedData = $request->validate([
            '*.id' => 'required|integer|min:1',
            '*.sort' => 'required|integer',
        ]);
        $this->customFieldModel()::customFieldSaveSort($validatedData);
    }

    public function moveField(Request $request) {
        $validatedData = $request->validate([
            'id' => 'required|integer',
            'group_id' => 'required|integer',
        ]);

        extract($validatedData);

        return $this->customFieldModel()::customFieldMove($id, $group_id);
    }

}
