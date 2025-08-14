<ul class="nav nav-tabs border-bottom" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ \Request::route()->getName() == 'recruitment.my-activities' ? 'active' : '' }}"
            href="{{ route('recruitment.my-activities') }}">Requested
            &nbsp;<span>({{ $requestCount }})</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ \Request::route()->getName() == 'recruitment.my-referal' ? 'active' : '' }}"
            href="{{ route('recruitment.my-referal') }}">My Referral
            &nbsp;<span>({{ $referralCount }})</span></a>
    </li>
</ul>
