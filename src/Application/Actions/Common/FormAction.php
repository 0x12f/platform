<?php declare(strict_types=1);

namespace App\Application\Actions\Common;

use App\Domain\AbstractAction;
use App\Domain\Entities\FileRelation;
use App\Domain\Entities\Form;
use App\Domain\Entities\Form\Data as FromData;
use App\Domain\Exceptions\HttpNotFoundException;
use App\Domain\Service\Form\DataService as FormDataService;
use App\Domain\Service\Form\FormService;

class FormAction extends AbstractAction
{
    protected function action(): \Slim\Http\Response
    {
        $formService = FormService::getWithContainer($this->container);
        $formDataService = FormDataService::getWithContainer($this->container);
        $form = $formService->read(['address' => $this->resolveArg('unique')]);

        /** @var Form $form */
        if ($form) {
            if (
                (
                    empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest'
                ) && !empty($_SERVER['HTTP_REFERER'])
            ) {
                $this->response = $this->response->withRedirect($_SERVER['HTTP_REFERER']);
            }

            if (!$form->getRecaptcha() || $this->isRecaptchaChecked()) {
                $remote = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? false;
                $data = $this->request->getParams();

                // CORS header sets
                foreach ($form->getOrigin() as $origin) {
                    if ($remote && mb_strpos($origin, $remote) >= 0) {
                        $this->response = $this->response->withHeader('Access-Control-Allow-Origin', $remote);

                        break;
                    }
                    if ($origin === '*') {
                        $this->response = $this->response->withHeader('Access-Control-Allow-Origin', '*');

                        break;
                    }
                }

                if ($this->response->hasHeader('Access-Control-Allow-Origin')) {
                    $this->response = $this->response->withHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
                }

                // prepare mailto
                $mailto = [];
                foreach (array_map('trim', $form->getMailto()) as $key => $value) {
                    $buf = array_map('trim', explode(':', $value));

                    if (count($buf) === 2) {
                        $mailto[$buf[0]] = $buf[1];
                    } else {
                        $mailto[] = $buf[0];
                    }
                }

                $isHtml = true;

                // prepare mail body
                if (
                    $form->getTemplate()
                    && $form->getTemplate() !== '<p><br></p>'
                ) {
                    $body = $this->renderer->fetchFromString($form->getTemplate(), $data);
                } else {
                    // no template, check post data for mail body
                    if (($buf = $this->request->getParam('body', false)) !== false) {
                        $body = $buf;
                    } else {
                        // json in mail
                        $body = json_encode(str_escape($data), JSON_UNESCAPED_UNICODE);
                        $isHtml = false;
                    }
                }

                // prepare form data
                $formData = $formDataService->create([
                    'form_uuid' => $form->getUuid(),
                    'message' => $body,
                ]);

                // prepare attachments
                $attachments = [];
                $json = [];
                if ($this->parameter('file_is_enabled', 'no') === 'yes') {
                    $formData = $this->processEntityFiles($formData);

                    foreach ($formData->getFiles() as $file) {
                        /**
                         * @var FromData     $formData
                         * @var FileRelation $file
                         */
                        $attachments[$file->getFileName()] = $file->getPublicPath();
                        $json[] = [
                            'uuid' => $file->getUuid()->toString(),
                            'name' => $file->getFileName(),
                            'order' => $file->getOrder(),
                            'comment' => $file->getComment(),
                            'internal' => $file->getInternalPath(),
                            'public' => $file->getPublicPath(),
                        ];
                    }
                }

                // check if duplication is enabled
                if (($duplicate = $form->getDuplicate()) !== '') {
                    // send json task
                    $task = new \App\Domain\Tasks\SendJSONTask($this->container);
                    $task->execute([
                        'url' => $duplicate,
                        'data' => $data,
                        'files' => $json,
                    ]);

                    // run worker
                    \App\Domain\AbstractTask::worker($task);
                }

                // send mail task
                $task = new \App\Domain\Tasks\SendMailTask($this->container);
                $task->execute([
                    'to' => $mailto,
                    'cc' => $form->getAuthorSend() && !empty($data['email']) ? $data['email'] : '',
                    'subject' => $form->getTitle(),
                    'body' => $body,
                    'isHtml' => $isHtml,
                    'attachments' => $attachments,
                ]);

                // run worker
                \App\Domain\AbstractTask::worker($task);

                return $this->respondWithJson(['status' => 'ok']);
            }

            $this->addError('grecaptcha', \App\Domain\References\Errors\Common::WRONG_GRECAPTCHA);
        } else {
            throw new HttpNotFoundException();
        }

        return $this->response->withStatus(500);
    }
}
