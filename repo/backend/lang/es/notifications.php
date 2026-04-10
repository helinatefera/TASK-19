<?php

return [
    'booking_confirmation' => [
        'title' => 'Reserva confirmada',
        'body' => 'Su reserva #:confirmation_number para :event ha sido confirmada para el :date.',
    ],
    'order_paid' => [
        'title' => 'Pago recibido',
        'body' => 'Se ha registrado un pago de :amount para el pedido #:confirmation_number.',
    ],
    'order_cancelled' => [
        'title' => 'Pedido cancelado',
        'body' => 'Su pedido #:confirmation_number ha sido cancelado. Motivo: :reason',
    ],
    'order_refunded' => [
        'title' => 'Pedido reembolsado',
        'body' => 'Se ha procesado un reembolso de :amount para el pedido #:confirmation_number.',
    ],
    'order_fulfilled' => [
        'title' => 'Pedido completado',
        'body' => 'Su pedido #:confirmation_number ha sido completado.',
    ],
    'campaign_approved' => [
        'title' => 'Campaña aprobada',
        'body' => 'Su campaña ":title" ha sido aprobada y ya está activa.',
    ],
    'campaign_rejected' => [
        'title' => 'Campaña rechazada',
        'body' => 'Su campaña ":title" ha sido rechazada. Motivo: :reason',
    ],
    'campaign_failed' => [
        'title' => 'La campaña no alcanzó su objetivo',
        'body' => 'La campaña ":title" no alcanzó su objetivo de financiación. Su contribución es elegible para un reembolso.',
    ],
    'refund_approved' => [
        'title' => 'Reembolso aprobado',
        'body' => 'Su solicitud de reembolso para el pedido #:confirmation_number ha sido aprobada.',
    ],
    'refund_rejected' => [
        'title' => 'Reembolso rechazado',
        'body' => 'Su solicitud de reembolso para el pedido #:confirmation_number ha sido rechazada.',
    ],
    'voucher_ready' => [
        'title' => 'Cupón disponible',
        'body' => 'Su cupón para el pedido #:confirmation_number ya está disponible. Código: :code',
    ],
    'booking_reminder' => [
        'title' => 'Recordatorio de reserva próxima',
        'body' => 'Recordatorio: Su reserva #:confirmation_number es en :hours horas en :venue.',
    ],
    'dispute_decided' => [
        'title' => 'Decisión de la disputa',
        'body' => 'Se ha tomado una decisión sobre su disputa: :decision',
    ],
    'anomaly_detected' => [
        'title' => 'Anomalía detectada',
        'body' => 'Se ha detectado una anomalía para el usuario :username. Tipo: :type. Se requiere revisión.',
    ],
];
