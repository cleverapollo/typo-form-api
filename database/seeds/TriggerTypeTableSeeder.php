<?php

use App\Models\TriggerType;
use App\Models\QuestionType;
use App\Models\Comparator;
use Illuminate\Database\Seeder;

class TriggerTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'id' => 1,
                'question_type' => 'Short answer',
                'comparator' => 'equals',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 2,
                'question_type' => 'Short answer',
                'comparator' => 'not equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 3,
                'question_type' => 'Short answer',
                'comparator' => 'less than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 4,
                'question_type' => 'Short answer',
                'comparator' => 'greater than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 5,
                'question_type' => 'Short answer',
                'comparator' => 'less than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 6,
                'question_type' => 'Short answer',
                'comparator' => 'greater than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 7,
                'question_type' => 'Short answer',
                'comparator' => 'contains',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 8,
                'question_type' => 'Short answer',
                'comparator' => 'does not contain',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 9,
                'question_type' => 'Short answer',
                'comparator' => 'starts with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 10,
                'question_type' => 'Short answer',
                'comparator' => 'ends with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 11,
                'question_type' => 'Short answer',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 12,
                'question_type' => 'Short answer',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 13,
                'question_type' => 'Short answer',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 14,
                'question_type' => 'Short answer',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],


            [
                'id' => 15,
                'question_type' => 'Paragraph',
                'comparator' => 'equals',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 16,
                'question_type' => 'Paragraph',
                'comparator' => 'not equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 17,
                'question_type' => 'Paragraph',
                'comparator' => 'less than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 18,
                'question_type' => 'Paragraph',
                'comparator' => 'greater than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 19,
                'question_type' => 'Paragraph',
                'comparator' => 'less than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 20,
                'question_type' => 'Paragraph',
                'comparator' => 'greater than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 21,
                'question_type' => 'Paragraph',
                'comparator' => 'contains',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 22,
                'question_type' => 'Paragraph',
                'comparator' => 'does not contain',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 23,
                'question_type' => 'Paragraph',
                'comparator' => 'starts with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 24,
                'question_type' => 'Paragraph',
                'comparator' => 'ends with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 25,
                'question_type' => 'Paragraph',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 26,
                'question_type' => 'Paragraph',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 27,
                'question_type' => 'Paragraph',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 28,
                'question_type' => 'Paragraph',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 29,
                'question_type' => 'Multiple choice',
                'comparator' => 'equals',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 30,
                'question_type' => 'Multiple choice',
                'comparator' => 'not equal to',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 31,
                'question_type' => 'Multiple choice',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 32,
                'question_type' => 'Multiple choice',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 33,
                'question_type' => 'Multiple choice',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 34,
                'question_type' => 'Multiple choice',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 35,
                'question_type' => 'Checkboxes',
                'comparator' => 'contains',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 36,
                'question_type' => 'Checkboxes',
                'comparator' => 'does not contain',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 37,
                'question_type' => 'Checkboxes',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 38,
                'question_type' => 'Checkboxes',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 39,
                'question_type' => 'Checkboxes',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 40,
                'question_type' => 'Checkboxes',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 41,
                'question_type' => 'Dropdown',
                'comparator' => 'equals',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 42,
                'question_type' => 'Dropdown',
                'comparator' => 'not equal to',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 43,
                'question_type' => 'Dropdown',
                'comparator' => 'contains',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 44,
                'question_type' => 'Dropdown',
                'comparator' => 'does not contain',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 45,
                'question_type' => 'Dropdown',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 46,
                'question_type' => 'Dropdown',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 47,
                'question_type' => 'Dropdown',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 48,
                'question_type' => 'Dropdown',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 49,
                'question_type' => 'File upload',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 50,
                'question_type' => 'File upload',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 51,
                'question_type' => 'Linear scale',
                'comparator' => 'equals',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 52,
                'question_type' => 'Linear scale',
                'comparator' => 'not equal to',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 53,
                'question_type' => 'Linear scale',
                'comparator' => 'less than',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 54,
                'question_type' => 'Linear scale',
                'comparator' => 'greater than',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 55,
                'question_type' => 'Linear scale',
                'comparator' => 'less than or equal to',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 56,
                'question_type' => 'Linear scale',
                'comparator' => 'greater than or equal to',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 57,
                'question_type' => 'Linear scale',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 58,
                'question_type' => 'Linear scale',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 59,
                'question_type' => 'Multiple choice grid',
                'comparator' => 'contains',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 60,
                'question_type' => 'Multiple choice grid',
                'comparator' => 'does not contain',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 61,
                'question_type' => 'Multiple choice grid',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 62,
                'question_type' => 'Multiple choice grid',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 63,
                'question_type' => 'Checkbox grid',
                'comparator' => 'contains',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 64,
                'question_type' => 'Checkbox grid',
                'comparator' => 'does not contain',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 65,
                'question_type' => 'Checkbox grid',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 66,
                'question_type' => 'Checkbox grid',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 67,
                'question_type' => 'Date',
                'comparator' => 'equals',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 68,
                'question_type' => 'Date',
                'comparator' => 'not equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 69,
                'question_type' => 'Date',
                'comparator' => 'less than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 70,
                'question_type' => 'Date',
                'comparator' => 'greater than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 71,
                'question_type' => 'Date',
                'comparator' => 'less than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 72,
                'question_type' => 'Date',
                'comparator' => 'greater than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 73,
                'question_type' => 'Date',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 74,
                'question_type' => 'Date',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 75,
                'question_type' => 'Time',
                'comparator' => 'equals',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 76,
                'question_type' => 'Time',
                'comparator' => 'not equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 77,
                'question_type' => 'Time',
                'comparator' => 'less than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 78,
                'question_type' => 'Time',
                'comparator' => 'greater than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 79,
                'question_type' => 'Time',
                'comparator' => 'less than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 80,
                'question_type' => 'Time',
                'comparator' => 'greater than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 81,
                'question_type' => 'Time',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 82,
                'question_type' => 'Time',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 83,
                'question_type' => 'ABN Lookup',
                'comparator' => 'equals',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 84,
                'question_type' => 'ABN Lookup',
                'comparator' => 'not equal to',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 85,
                'question_type' => 'ABN Lookup',
                'comparator' => 'less than',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 86,
                'question_type' => 'ABN Lookup',
                'comparator' => 'greater than',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 87,
                'question_type' => 'ABN Lookup',
                'comparator' => 'less than or equal to',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 88,
                'question_type' => 'ABN Lookup',
                'comparator' => 'greater than or equal to',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 89,
                'question_type' => 'ABN Lookup',
                'comparator' => 'starts with',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 90,
                'question_type' => 'ABN Lookup',
                'comparator' => 'ends with',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 91,
                'question_type' => 'ABN Lookup',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 92,
                'question_type' => 'ABN Lookup',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 93,
                'question_type' => 'Number',
                'comparator' => 'equals',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 94,
                'question_type' => 'Number',
                'comparator' => 'not equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 95,
                'question_type' => 'Number',
                'comparator' => 'less than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 96,
                'question_type' => 'Number',
                'comparator' => 'greater than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 97,
                'question_type' => 'Number',
                'comparator' => 'less than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 98,
                'question_type' => 'Number',
                'comparator' => 'greater than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 99,
                'question_type' => 'Number',
                'comparator' => 'contains',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 100,
                'question_type' => 'Number',
                'comparator' => 'does not contain',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 101,
                'question_type' => 'Number',
                'comparator' => 'starts with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 102,
                'question_type' => 'Number',
                'comparator' => 'ends with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 103,
                'question_type' => 'Number',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 104,
                'question_type' => 'Number',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 105,
                'question_type' => 'Number',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 106,
                'question_type' => 'Number',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 107,
                'question_type' => 'Decimal',
                'comparator' => 'equals',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 108,
                'question_type' => 'Decimal',
                'comparator' => 'not equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 109,
                'question_type' => 'Decimal',
                'comparator' => 'less than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 110,
                'question_type' => 'Decimal',
                'comparator' => 'greater than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 111,
                'question_type' => 'Decimal',
                'comparator' => 'less than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 112,
                'question_type' => 'Decimal',
                'comparator' => 'greater than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 113,
                'question_type' => 'Decimal',
                'comparator' => 'contains',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 114,
                'question_type' => 'Decimal',
                'comparator' => 'does not contain',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 115,
                'question_type' => 'Decimal',
                'comparator' => 'starts with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 116,
                'question_type' => 'Decimal',
                'comparator' => 'ends with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 117,
                'question_type' => 'Decimal',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 118,
                'question_type' => 'Decimal',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 119,
                'question_type' => 'Decimal',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 120,
                'question_type' => 'Decimal',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 121,
                'question_type' => 'Email',
                'comparator' => 'equals',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 122,
                'question_type' => 'Email',
                'comparator' => 'not equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 123,
                'question_type' => 'Email',
                'comparator' => 'less than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 124,
                'question_type' => 'Email',
                'comparator' => 'greater than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 125,
                'question_type' => 'Email',
                'comparator' => 'less than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 126,
                'question_type' => 'Email',
                'comparator' => 'greater than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 127,
                'question_type' => 'Email',
                'comparator' => 'contains',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 128,
                'question_type' => 'Email',
                'comparator' => 'does not contain',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 129,
                'question_type' => 'Email',
                'comparator' => 'starts with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 130,
                'question_type' => 'Email',
                'comparator' => 'ends with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 131,
                'question_type' => 'Email',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 132,
                'question_type' => 'Email',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 133,
                'question_type' => 'Email',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 134,
                'question_type' => 'Email',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 135,
                'question_type' => 'Percent',
                'comparator' => 'equals',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 136,
                'question_type' => 'Percent',
                'comparator' => 'not equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 137,
                'question_type' => 'Percent',
                'comparator' => 'less than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 138,
                'question_type' => 'Percent',
                'comparator' => 'greater than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 139,
                'question_type' => 'Percent',
                'comparator' => 'less than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 140,
                'question_type' => 'Percent',
                'comparator' => 'greater than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 141,
                'question_type' => 'Percent',
                'comparator' => 'contains',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 142,
                'question_type' => 'Percent',
                'comparator' => 'does not contain',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 143,
                'question_type' => 'Percent',
                'comparator' => 'starts with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 144,
                'question_type' => 'Percent',
                'comparator' => 'ends with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 145,
                'question_type' => 'Percent',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 146,
                'question_type' => 'Percent',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 147,
                'question_type' => 'Percent',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 148,
                'question_type' => 'Percent',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 149,
                'question_type' => 'Phone number',
                'comparator' => 'equals',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 150,
                'question_type' => 'Phone number',
                'comparator' => 'not equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 151,
                'question_type' => 'Phone number',
                'comparator' => 'less than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 152,
                'question_type' => 'Phone number',
                'comparator' => 'greater than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 153,
                'question_type' => 'Phone number',
                'comparator' => 'less than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 154,
                'question_type' => 'Phone number',
                'comparator' => 'greater than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 155,
                'question_type' => 'Phone number',
                'comparator' => 'contains',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 156,
                'question_type' => 'Phone number',
                'comparator' => 'does not contain',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 157,
                'question_type' => 'Phone number',
                'comparator' => 'starts with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 158,
                'question_type' => 'Phone number',
                'comparator' => 'ends with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 159,
                'question_type' => 'Phone number',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 160,
                'question_type' => 'Phone number',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 161,
                'question_type' => 'Phone number',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 162,
                'question_type' => 'Phone number',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 163,
                'question_type' => 'Address',
                'comparator' => 'equals',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 164,
                'question_type' => 'Address',
                'comparator' => 'not equal to',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 165,
                'question_type' => 'Address',
                'comparator' => 'less than',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 166,
                'question_type' => 'Address',
                'comparator' => 'greater than',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 167,
                'question_type' => 'Address',
                'comparator' => 'less than or equal to',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 168,
                'question_type' => 'Address',
                'comparator' => 'greater than or equal to',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 169,
                'question_type' => 'Address',
                'comparator' => 'starts with',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 170,
                'question_type' => 'Address',
                'comparator' => 'ends with',
                'answer' => true,
                'value' => true
            ],
            [
                'id' => 171,
                'question_type' => 'Address',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 172,
                'question_type' => 'Address',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 173,
                'question_type' => 'URL',
                'comparator' => 'equals',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 174,
                'question_type' => 'URL',
                'comparator' => 'not equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 175,
                'question_type' => 'URL',
                'comparator' => 'less than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 176,
                'question_type' => 'URL',
                'comparator' => 'greater than',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 177,
                'question_type' => 'URL',
                'comparator' => 'less than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 178,
                'question_type' => 'URL',
                'comparator' => 'greater than or equal to',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 179,
                'question_type' => 'URL',
                'comparator' => 'contains',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 180,
                'question_type' => 'URL',
                'comparator' => 'does not contain',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 181,
                'question_type' => 'URL',
                'comparator' => 'starts with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 182,
                'question_type' => 'URL',
                'comparator' => 'ends with',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 183,
                'question_type' => 'URL',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 184,
                'question_type' => 'URL',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 185,
                'question_type' => 'URL',
                'comparator' => 'in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 186,
                'question_type' => 'URL',
                'comparator' => 'not in list',
                'answer' => false,
                'value' => true
            ],
            [
                'id' => 187,
                'question_type' => 'Country',
                'comparator' => 'equals',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 188,
                'question_type' => 'Country',
                'comparator' => 'not equal to',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 189,
                'question_type' => 'Country',
                'comparator' => 'less than',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 190,
                'question_type' => 'Country',
                'comparator' => 'greater than',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 191,
                'question_type' => 'Country',
                'comparator' => 'less than or equal to',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 192,
                'question_type' => 'Country',
                'comparator' => 'greater than or equal to',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 193,
                'question_type' => 'Country',
                'comparator' => 'starts with',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 194,
                'question_type' => 'Country',
                'comparator' => 'ends with',
                'answer' => true,
                'value' => false
            ],
            [
                'id' => 195,
                'question_type' => 'Country',
                'comparator' => 'is null',
                'answer' => false,
                'value' => false
            ],
            [
                'id' => 196,
                'question_type' => 'Country',
                'comparator' => 'is not null',
                'answer' => false,
                'value' => false
            ]
        ];

        foreach ($items as $item) {
            TriggerType::updateOrCreate(['id' => $item['id']], [
                'question_type_id' => QuestionType::firstOrCreate(['type' => $item['question_type']])->id,
                'comparator_id' => Comparator::firstOrCreate(['comparator' => $item['comparator']])->id,
                'answer' => $item['answer'],
                'value' => $item['value']
            ]);
        }
    }
}