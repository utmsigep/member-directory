<?php

namespace App\Service;

use App\Entity\Member;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MemberUpdateTokenGenerator
{
    public function __construct(
        #[Autowire('%kernel.secret%')]
        private readonly string $secret,
    ) {
    }

    public function generate(Member $member): string
    {
        return hash_hmac('sha256', json_encode([
            $member->getId(),
            $member->getExternalIdentifier(),
            $member->getUpdatedAt(),
        ]), $this->secret);
    }

    public function isValid(Member $member, ?string $token): bool
    {
        if (!$token) {
            return false;
        }

        return hash_equals($this->generate($member), $token);
    }
}
