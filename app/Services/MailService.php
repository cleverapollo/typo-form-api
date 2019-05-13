<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailService {
    /**
     * Parse and replace mapped data in the body. The map data array should be in the following 
     * format:
     * [
     *  'search_key' => 'path.to.value'   // eg: 'first_name' => 'invitation.firstname'
     * ]
     *
     * @param string $body
     * @param Array $data
     * @param Array $map
     * @return void
     */
    protected function applyMailMerge($body, $data, $map) 
    {
        $keys = collect($map)->keys()->map(function($key) {
            return '{{' . $key . '}}';
        });

        $values = collect($map)->map(function($lookupPath) use ($data) {
            return data_get($data, $lookupPath, '');
        });

        return str_replace($keys->toArray(), $values->toArray(), $body);
    }

    /**
     * Extract out key fields such as email, subject, body from the passed data. This allows each
     * consumer of the service to control where/how the data is stored and avoids this service
     * from "knowing" about an invitation
     *
     * @param Array $data
     * @param Array $map
     * @return void
     */
    protected function extract($data, $map)
    {
        return collect($map)->map(function($lookupPath) use ($data) {
            return data_get($data, $lookupPath, '');
        });
    }

    /**
     * Convert email addresses from string to array
     *
     * @param String $email
     * @return string
     */
    protected function formatEmailAddresses ($email) {
        $email = str_replace(' ', '', $email);
        $email = str_replace(';', ',', $email);
        $email = explode(',', $email);
        return $email;
    }

    public function send($data, $map, $mergeMap)
    {
        ['email' => $email, 'body' => $body, 'subject' => $subject, 'cc' => $cc, 'bcc' => $bcc] = $this->extract($data, $map);

        $body = $this->applyMailMerge($body, $data, $mergeMap);

        Mail::send([], [], function ($message) use ($email, $body, $subject, $cc, $bcc) {
            $message
                ->to(strtolower($email))
                ->from(ENV('MAIL_FROM_ADDRESS'))
                ->subject($subject)
                ->setBody($body, 'text/html');
            
            if (!empty($cc)) {
                $message->cc($this->formatEmailAddresses($cc));
            }

            if (!empty($bcc)) {
                $message->bcc($this->formatEmailAddresses($bcc));
            }
        });
    }
}