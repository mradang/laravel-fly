<?php

namespace mradang\LaravelFly\Services;

use mradang\LaravelFly\Models\CustomFieldValue as FieldValue;

class CustomFieldValueService {

    public static function save($class, $key, array $data) {
        $value = FieldValue::firstOrNew([
            'valuetable_type' => $class,
            'valuetable_id' => $key,
        ]);
        $value->data = $data;
        $value->save();
        return $value;
    }

    public static function saveItem($class, $key, array $item) {
        $value = FieldValue::firstOrNew([
            'valuetable_type' => $class,
            'valuetable_id' => $key,
        ]);

        $data = $value->data ?: [];
        $pos = -1;
        foreach ($data as $index => $row) {
            if ($row['field_id'] === $item['field_id']) {
                $data[$index] = $item;
                $pos = $index;
            }
        }
        if ($pos === -1) {
            $data[] = $item;
        }

        $value->data = $data;
        $value->save();
        return $value;
    }

    public static function getItem($class, $key, $field_id) {
        $value = FieldValue::firstOrNew([
            'valuetable_type' => $class,
            'valuetable_id' => $key,
        ]);

        $data = $value->data ?: [];
        $pos = -1;
        foreach ($data as $index => $row) {
            if ($row['field_id'] === $field_id) {
                $pos = $index;
                break;
            }
        }
        return $pos === -1 ? null : $data[$pos]['value'];
    }

    public static function get($class, $key) {
        $value = FieldValue::where([
            'valuetable_type' => $class,
            'valuetable_id' => $key,
        ])->first();
        return $value ? $value->data : [];
    }

    public static function delete($class, $key) {
        FieldValue::where([
            'valuetable_type' => $class,
            'valuetable_id' => $key,
        ])->delete();
    }

}
