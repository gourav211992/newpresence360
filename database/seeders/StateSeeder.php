<?php

namespace Database\Seeders;
use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $state = [
        ['id' => 1, 'name' => 'Andaman and Nicobar Islands', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 2, 'name' => 'Andhra Pradesh', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 3, 'name' => 'Arunachal Pradesh', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 4, 'name' => 'Assam', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 5, 'name' => 'Bihar', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 6, 'name' => 'Chandigarh', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 7, 'name' => 'Chhattisgarh', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 8, 'name' => 'Dadra and Nagar Haveli', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 9, 'name' => 'Daman and Diu', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 10, 'name' => 'Delhi', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 11, 'name' => 'Goa', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 12, 'name' => 'Gujarat', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 13, 'name' => 'Haryana', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 14, 'name' => 'Himachal Pradesh', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 15, 'name' => 'Jammu and Kashmir', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 16, 'name' => 'Jharkhand', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 17, 'name' => 'Karnataka', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 18, 'name' => 'Kenmore', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 19, 'name' => 'Kerala', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 20, 'name' => 'Lakshadweep', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 21, 'name' => 'Madhya Pradesh', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 22, 'name' => 'Maharashtra', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 23, 'name' => 'Manipur', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 24, 'name' => 'Meghalaya', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 25, 'name' => 'Mizoram', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 26, 'name' => 'Nagaland', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 27, 'name' => 'Narora', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 28, 'name' => 'Natwar', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 29, 'name' => 'Odisha', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 30, 'name' => 'Paschim Medinipur', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 31, 'name' => 'Pondicherry', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 32, 'name' => 'Punjab', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 33, 'name' => 'Rajasthan', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 34, 'name' => 'Sikkim', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 35, 'name' => 'Tamil Nadu', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 36, 'name' => 'Telangana', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 37, 'name' => 'Tripura', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 38, 'name' => 'Uttar Pradesh', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 39, 'name' => 'Uttarakhand', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 40, 'name' => 'Vaishali', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ['id' => 41, 'name' => 'West Bengal', 'country_id' => 101, 'status' => 'active', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null]
    ];
      State::insert($state);
    }
}
