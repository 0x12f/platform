<?php declare(strict_types=1);

namespace App\Domain\Tasks;

use App\Application\Mail;
use App\Domain\AbstractTask;

class SendMailTask extends AbstractTask
{
    public const TITLE = 'Отправка почты';

    public function execute(array $params = []): \App\Domain\Entities\Task
    {
        $default = [
            'subject' => '',
            'to' => '', // string|array(address=>name)
            'cc' => '', // string|array(address=>name)
            'bcc' => '', // string|array(address=>name)
            'body' => '',
            'isHtml' => false,
            'attachments' => [],
        ];
        $params = array_merge($default, $params);

        return parent::execute($params);
    }

    protected function action(array $args = []): void
    {
        $args = array_merge(
            $this->parameter(
                [
                    'smtp_from', 'smtp_from_name',
                    'smtp_login', 'smtp_pass',
                    'smtp_host', 'smtp_port',
                    'smtp_secure',
                ]
            ),
            [
                'subject' => $args['subject'] ? $args['subject'] : $this->parameter('smtp_subject', 'WebSpaceEngine | Default subject'),
                'to' => $args['to'],
                'cc' => $args['cc'],
                'bcc' => $args['bcc'],
                'body' => $args['body'],
                'isHtml' => (bool) $args['isHtml'],
                'attachments' => (array) $args['attachments'],
                'auto_send' => true,
            ]
        );

        if ($args['smtp_host'] && $args['smtp_login'] && $args['smtp_pass']) {
            $mail = Mail::send($args);

            if ($mail !== false) {
                if (!$mail->isError()) {
                    $this->logger->info('Mail is sent', ['mailto' => $args['to']]);
                    $this->setStatusDone('ok');
                } else {
                    $this->logger->warning('Mail will not sent', ['mailto' => $args['to'], 'error' => $mail->ErrorInfo]);
                    $this->setStatusFail($mail->ErrorInfo);
                }

                return;
            }
        }

        $this->setStatusFail();
    }
}
