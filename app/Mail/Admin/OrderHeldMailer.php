<?php

namespace App\Mail\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class OrderHeldMailer extends AdminMailer
{
    use Queueable, SerializesModels;

    /**
     * @var Order
     */
    private Order $order;

    /**
     * OrderCreated constructor.
     * @param Order $order
     * @param User $user
     */
    public function __construct(Order $order, User $user)
    {
        parent::__construct('order_held', $order);

        $this->order = $order;
        $this->entity = $order;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return void
     */
    public function build()
    {
        $data = $this->getData();

        $this->setSubject($data);
        $this->setMessage($data);
        $this->execute($this->buildMessage());
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        return [
            'total'    => $this->order->getFormattedTotal(),
            'customer' => $this->order->customer->present()->name(),
            'order'    => $this->order->getNumber(),
        ];
    }

    /**
     * @return array
     */
    private function buildMessage(): array
    {
        return [
            'title'       => $this->subject,
            'body'        => $this->message,
            'url'         => $this->getUrl() . 'orders/' . $this->order->id,
            'button_text' => trans('texts.view_order'),
            'signature'   => isset($this->order->account->settings->email_signature) ? $this->order->account->settings->email_signature : '',
            'logo'        => $this->order->account->present()->logo(),
        ];
    }
}
