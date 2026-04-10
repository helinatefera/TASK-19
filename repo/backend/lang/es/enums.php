<?php

return [
    'campaign_status' => [
        'draft' => 'Borrador',
        'pending_review' => 'Pendiente de revisión',
        'published' => 'Publicada',
        'fundraising' => 'En recaudación',
        'success' => 'Exitosa',
        'failure' => 'Fallida',
        'closed' => 'Cerrada',
    ],
    'campaign_visibility' => [
        'online' => 'En línea',
        'offline' => 'Fuera de línea',
    ],
    'order_status' => [
        'pending' => 'Pendiente',
        'confirmed' => 'Confirmado',
        'fulfilled' => 'Completado',
        'cancelled' => 'Cancelado',
        'refunded' => 'Reembolsado',
        'after_sales' => 'Posventa',
    ],
    'order_type' => [
        'contribution' => 'Contribución',
        'reservation' => 'Reserva',
    ],
    'payment_method' => [
        'cash' => 'Efectivo',
        'card_on_file' => 'Tarjeta registrada',
    ],
    'payment_status' => [
        'pending' => 'Pendiente',
        'completed' => 'Completado',
        'failed' => 'Fallido',
    ],
    'voucher_status' => [
        'active' => 'Activo',
        'redeemed' => 'Canjeado',
        'expired' => 'Expirado',
        'revoked' => 'Revocado',
    ],
    'review_side' => [
        'user_to_creator' => 'Usuario a creador',
        'creator_to_user' => 'Creador a usuario',
    ],
    'notification_type' => [
        'inbox' => 'Bandeja de entrada',
        'alert' => 'Alerta',
    ],
    'refund_status' => [
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
    ],
    'after_sales_status' => [
        'submitted' => 'Enviada',
        'under_review' => 'En revisión',
        'approved' => 'Aprobada',
        'rejected' => 'Rechazada',
    ],
    'after_sales_type' => [
        'damaged' => 'Dañado',
        'defective' => 'Defectuoso',
        'missing' => 'Extraviado',
        'unsatisfactory' => 'Insatisfactorio',
    ],
    'dispute_status' => [
        'open' => 'Abierta',
        'under_review' => 'En revisión',
        'resolved' => 'Resuelta',
        'escalated' => 'Escalada',
    ],
    'restriction_level' => [
        'none' => 'Ninguna',
        'gray' => 'Lista gris',
        'black' => 'Lista negra',
    ],
    'milestone_status' => [
        'pending' => 'Pendiente',
        'in_progress' => 'En progreso',
        'completed' => 'Completado',
    ],
    'fulfillment_type' => [
        'digital' => 'Digital',
        'physical' => 'Físico',
        'event' => 'Evento',
    ],
    'user_role' => [
        'user' => 'Usuario',
        'creator' => 'Creador',
        'moderator' => 'Moderador',
        'staff' => 'Personal',
        'admin' => 'Administrador',
    ],
];
