<?php

namespace App\Helpers;

class MercadoLibreShippingHelper
{
    /**
     * Detecta quién paga el envío en Mercado Libre
     *
     * @param array $order    Respuesta de /orders/{id} (opcional)
     * @param array $shipment Respuesta de /shipments/{id} (opcional)
     * @return array
     */
    public static function whoPays(array $order = [], array $shipment = []): array
    {
        // ===============================
        // 1️⃣ CASO IDEAL → shipping.costs
        // ===============================
        $costs = $order['shipping']['costs']
              ?? $shipment['shipping']['costs']
              ?? null;

        if (is_array($costs)) {
            $buyer  = $costs['buyer']  ?? 0;
            $seller = $costs['seller'] ?? 0;

            if ($buyer > 0 && $seller > 0) {
                return self::response('COMPARTIDO', $buyer, $seller, 'shipping.costs');
            }

            if ($buyer > 0) {
                return self::response('COMPRADOR', $buyer, 0, 'shipping.costs');
            }

            if ($seller > 0) {
                return self::response('VENDEDOR', 0, $seller, 'shipping.costs');
            }

            return self::response('GRATIS', 0, 0, 'shipping.costs');
        }

        // ==================================
        // 2️⃣ shipping_option.cost (shipment)
        // ==================================
        $optionCost = $shipment['shipping_option']['cost'] ?? null;

        if ($optionCost !== null) {
            if ($optionCost > 0) {
                return self::response('COMPRADOR', $optionCost, 0, 'shipping_option.cost');
            }

            // cost = 0 → vendedor o promo
            $baseCost = $shipment['base_cost'] ?? 0;

            if ($baseCost > 0) {
                return self::response('VENDEDOR', 0, $baseCost, 'base_cost');
            }

            return self::response('GRATIS', 0, 0, 'shipping_option.cost');
        }

        // ==================================
        // 3️⃣ Logistic type (fallback fuerte)
        // ==================================
        $logisticType = $shipment['logistic_type']
            ?? $order['shipping']['logistic_type']
            ?? null;

        if (in_array($logisticType, [
            'fulfillment',
            'cross_docking',
            'xd_drop_off',
            'drop_off'
        ])) {
            return self::response('VENDEDOR', 0, null, 'logistic_type');
        }

        // ===============================
        // 4️⃣ Último fallback
        // ===============================
        return self::response('DESCONOCIDO', null, null, 'fallback');
    }

    private static function response(
        string $who,
        $buyerCost,
        $sellerCost,
        string $source
    ): array {
        return [
            'who_pays'     => $who,
            'buyer_cost'   => $buyerCost,
            'seller_cost'  => $sellerCost,
            'detected_by'  => $source,
        ];
    }
}
