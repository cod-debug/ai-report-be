<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public const USER_ROLE_ADMIN = 1;
    public const USER_ROLE_ALUMNI = 2;
    public const USER_ROLE_EMPLOYER = 3;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'civil_status',
        'phone_number',
        'mobile_number',
        'username',
        'email',
        'password',
        'user_role',
        'status',
        'department_id',
        'course_id',
        'permanent_address_region_id',
        'permanent_address_province_id',
        'permanent_address_city_id',
        'permanent_address_barangay_id',
        'permanent_address_1',
        'permanent_address_2',
        'present_address_region_id',
        'present_address_province_id',
        'present_address_city_id',
        'present_address_barangay_id',
        'present_address_1',
        'present_address_2',
        'year_graduated',
        'designation',
        'job_description',
        'about',
        'employment_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function workExperiences(){
        return $this->hasMany(UserProfileWorkExperienceModel::class, 'user_id');
    }

    public function education(){
        return $this->hasMany(UserProfileEducationModel::class, 'user_id');
    }

    public function permanentAddressRegion()
    {
        return $this->belongsTo(PhilippineRegion::class, 'permanent_address_region_id');
    }

    public function permanentAddressProvince()
    {
        return $this->belongsTo(PhilippineProvince::class, 'permanent_address_province_id');
    }

    public function permanentAddressCity()
    {
        return $this->belongsTo(PhilippineCity::class, 'permanent_address_city_id');
    }

    public function permanentAddressBarangay()
    {
        return $this->belongsTo(PhilippineBarangay::class, 'permanent_address_barangay_id');
    }

    // Custom function to get the full permanent address as JSON
    public function getPermanentAddress()
    {
        return [
            'address_line_1' => $this->permanent_address_1,
            'address_line_2' => $this->permanent_address_2,
            'barangay' => $this->permanentAddressBarangay ? [
                    'name' => $this->permanentAddressBarangay->name,
                    'id' => $this->permanentAddressBarangay->id,
                ]
                : null,
            'city' => $this->permanentAddressCity ? [
                    'name' => $this->permanentAddressCity->name,
                    'id' => $this->permanentAddressCity->id,
                ]
                : null,
            'province' => $this->permanentAddressProvince ? [
                    'name' => $this->permanentAddressProvince->name,
                    'id' => $this->permanentAddressProvince->id,
                ]
                : null,
            'region' => $this->permanentAddressRegion ? [
                    'name' => $this->permanentAddressRegion->name,
                    'id' => $this->permanentAddressRegion->id,
                ]
                : null,
        ];
    }

    public function presentAddressRegion()
    {
        return $this->belongsTo(PhilippineRegion::class, 'present_address_region_id');
    }

    public function presentAddressProvince()
    {
        return $this->belongsTo(PhilippineProvince::class, 'present_address_province_id');
    }

    public function presentAddressCity()
    {
        return $this->belongsTo(PhilippineCity::class, 'present_address_city_id');
    }

    public function presentAddressBarangay()
    {
        return $this->belongsTo(PhilippineBarangay::class, 'present_address_barangay_id');
    }

    // Custom function to get the full peresent address as JSON
    public function getPresentAddress()
    {
        return [
            'address_line_1' => $this->present_address_1,
            'address_line_2' => $this->present_address_2,
            'barangay' => $this->presentAddressBarangay ?  [
                    'name' => $this->presentAddressBarangay->name,
                    'id' => $this->presentAddressBarangay->id,
                ]
                : null,
            'city' => $this->presentAddressCity ? [
                    'name' => $this->presentAddressCity->name,
                    'id' => $this->presentAddressCity->id,
                ]
                : null,
            'province' => $this->presentAddressProvince ? [
                    'name' => $this->presentAddressProvince->name,
                    'id' => $this->presentAddressProvince->id,
                ]
                : null,
            'region' => $this->presentAddressRegion ? [
                    'name' => $this->presentAddressRegion->name,
                    'id' => $this->presentAddressRegion->id,
                ]
                : null,
        ];
    }

    public function course(){
        return $this->belongsTo(CourseModel::class, 'course_id');
    }

    public function department(){
        return $this->belongsTo(DepartmentModel::class, 'department_id');
    }

    public function skills(){
        return $this->hasMany(UserProfileSkillModel::class, 'user_id');
    }

    public function company(){
        return $this->belongsToMany(UserCompanyDetailModel:: class, 'user_company_pivot', 'user_id', 'user_company_detail_id');
    }

    public function jobApplications(){
        return $this->hasMany(UserJobPostingApplicationModel::class, 'user_id');
    }

    public function hasJobApplications($job_posting_id, $status = null)
    {
        $query = $this->jobApplications();

        if ($status) {
            $query->where('status', $status);
        }

        if ($job_posting_id) {
            $query->where('job_posting_id', $job_posting_id);
        }

        return $query->exists();
    }
}
