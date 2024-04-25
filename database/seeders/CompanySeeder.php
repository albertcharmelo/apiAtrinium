<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        // Create a company
        $company = new Company();
        $company->name = 'Company Name';
        $company->email = 'example@gmail.com';
        $company->phone = '123456789';
        $company->address = 'Company Address';
        $company->document_type = 'cif';
        $company->document_number = '12345678A';
        $company->status = 'active';
        $company->save();

        $user->company()->save($company);
    }
}
