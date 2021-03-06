<?php

namespace App\Emails\ReferralCompleted;

use App\Emails\Email;

class NotifyRefereeEmail extends Email
{
    /**
     * @return string
     */
    protected function getTemplateId(): string
    {
        return config('tlr.notifications_template_ids.referral_completed.notify_referee.email');
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return <<<'EOT'
Hi ((REFEREE_NAME)),

The referral you made to ((SERVICE_NAME)) has been marked as complete. Referral ID: ((REFERRAL_ID)).

Your client should have been contacted by now, but if they haven’t then please contact them on ((SERVICE_PHONE)) or by email at ((SERVICE_EMAIL)).

If you would like to leave any feedback on the referral or get in touch with us, you can contact us at info@connectedtogether.org.uk.

Alternatively, you can complete our feedback form:
https://docs.google.com/forms/d/e/1FAIpQLSe38Oe0vsqLRQbcBjYrGzMooBJKkYqFWAlHy4dcgwJnMFg9dQ/viewform?usp=pp_url&entry.400427747=((REFERRAL_ID)).

Many thanks,

The Connected Together team
EOT;
    }

    /**
     * @inheritDoc
     */
    public function getSubject(): string
    {
        return 'Referral Completed';
    }
}
