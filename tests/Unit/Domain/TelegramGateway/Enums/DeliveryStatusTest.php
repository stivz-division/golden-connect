<?php

use App\Domain\TelegramGateway\Enums\DeliveryStatus;

it('has all expected cases', function () {
    expect(DeliveryStatus::cases())->toHaveCount(5);
});

it('can be created from string value', function () {
    expect(DeliveryStatus::from('sent'))->toBe(DeliveryStatus::Sent);
    expect(DeliveryStatus::from('delivered'))->toBe(DeliveryStatus::Delivered);
    expect(DeliveryStatus::from('read'))->toBe(DeliveryStatus::Read);
    expect(DeliveryStatus::from('expired'))->toBe(DeliveryStatus::Expired);
    expect(DeliveryStatus::from('revoked'))->toBe(DeliveryStatus::Revoked);
});

it('throws on invalid value', function () {
    DeliveryStatus::from('invalid');
})->throws(ValueError::class);
