<?php

namespace App\Services\AccountFees;

/**
 * Matches Strapi transaction relations back to a local account array.
 *
 * Strapi can return relations as numeric ids, document ids, account numbers,
 * nested data/attributes arrays, or lists. This helper keeps that matching
 * logic reusable for fee calculations and transaction filtering.
 */
class AccountTransactionMatcher
{
    /**
     * @param  mixed  $relatedAccount  Strapi relation as id, documentId, or account array.
     * @param  array<string, mixed>  $account
     */
    public static function matchesAccount(mixed $relatedAccount, array $account): bool
    {
        if ($relatedAccount === null) {
            return false;
        }

        if (is_numeric($relatedAccount)) {
            return (string) $relatedAccount === (string) ($account['id'] ?? '')
                || (string) $relatedAccount === (string) ($account['accountNumber'] ?? '');
        }

        if (! is_array($relatedAccount)) {
            return false;
        }

        if (array_is_list($relatedAccount)) {
            return collect($relatedAccount)->contains(fn (mixed $accountRelation): bool => self::matchesAccount($accountRelation, $account));
        }

        if (isset($relatedAccount['data']) && is_array($relatedAccount['data'])) {
            return self::matchesAccount($relatedAccount['data'], $account);
        }

        if (isset($relatedAccount['attributes']) && is_array($relatedAccount['attributes'])) {
            return self::matchesAccount($relatedAccount['attributes'] + [
                'id' => $relatedAccount['id'] ?? null,
                'documentId' => $relatedAccount['documentId'] ?? null,
            ], $account);
        }

        return self::sameValue($relatedAccount['id'] ?? null, $account['id'] ?? null)
            || self::sameValue($relatedAccount['documentId'] ?? null, $account['documentId'] ?? null)
            || self::sameValue($relatedAccount['accountNumber'] ?? null, $account['accountNumber'] ?? null);
    }

    private static function sameValue(mixed $left, mixed $right): bool
    {
        if ($left === null || $right === null) {
            return false;
        }

        return (string) $left === (string) $right;
    }
}
