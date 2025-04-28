<?php

namespace App\Helpers;

class CommonHelper
{
    const PAGE_LENGTH_10 = 10;
    const PAGE_LENGTH_20 = 20;
    const PAGE_LENGTH_50 = 50;
    const PAGE_LENGTH_100 = 100;
    const PAGE_LENGTH_2000 = 2000;
    const PAGE_LENGTH_1000 = 1000;
    const PAGE_LENGTH_10000 = 10000;

    const PAGE_LENGTHS = [
        self::PAGE_LENGTH_10,
        self::PAGE_LENGTH_20,
        self::PAGE_LENGTH_50,
        self::PAGE_LENGTH_100,
    ];
    
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const HIGH = 'high';
    const LOW = 'low';
    const MEDIUM = 'medium';

    const PRIORITY = [
       self::HIGH,
       self::MEDIUM,
       self::LOW
    ];

    
    const INTERVIEW_SCHEDULED = 'interview-scheduled';
    const ASSIGNED = 'assigned';
    const APPROVED_FORWARD = 'approved-forward';
    const FINAL_APPROVED = 'final-approved';
    const JOB_CREATED = 'job-created';
    const ONHOLD = 'onhold';
    const REVOKED = 'revoked';
    const QUALIFIED = 'qualified';
    const NOT_QUALIFIED = 'not-qualified';
    const CANDIDATE_REJECTED = 'candidate-rejected';
    const CANDIDATE_APPROVED = 'candidate-approved';
    const OPEN = 'open';
    const JOB = 'job';
	const INTERVIEW = 'interview';
	const CANDIDATE = 'candidate';
	const REJECTED = 'rejected';
	const PENDING = 'pending';
	const CLOSED = 'closed';

    const JOB_REQUEST_STATUS = [
        self::APPROVED_FORWARD,
        self::FINAL_APPROVED,
        self::REJECTED,
        self::REVOKED,
        self::PENDING
    ];

    const JOB_STATUS = [
        self::OPEN,
        self::CLOSED,
    ];

    const EMPLOYEMENT_TYPE = [
        'Full Time',
        'Part Time',
        'Trainee'
    ];

    const WORK_MODE = [
        'In Office',
        'REMOTE'
    ];

    public static function dateFormat($date)
    {
        $date = $date ? date('d-m-Y', strtotime($date)) : '';
        return $date;
    }
}