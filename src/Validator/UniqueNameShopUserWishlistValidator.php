<?php

declare(strict_types=1);

namespace BitBag\SyliusVueStorefront2Plugin\Validator;

use BitBag\SyliusWishlistPlugin\Checker\WishlistNameCheckerInterface;
use BitBag\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class UniqueNameShopUserWishlistValidator extends ConstraintValidator
{
    private Security $security;
    private WishlistRepositoryInterface $wishlistRepository;
    private WishlistNameCheckerInterface $wishlistNameChecker;

    public function __construct(
        Security $security,
        WishlistRepositoryInterface $wishlistRepository,
        WishlistNameCheckerInterface $wishlistNameChecker
    ) {
        $this->security = $security;
        $this->wishlistRepository = $wishlistRepository;
        $this->wishlistNameChecker = $wishlistNameChecker;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof  UniqueNameShopUserWishlist) {
            throw new UnexpectedTypeException($constraint, UniqueNameShopUserWishlist::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->isNameExist($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    public function isNameExist(string $value): bool
    {
        $user = $this->security->getUser();
        $wishlists = $this->wishlistRepository->findAllByShopUser($user->getId());

        foreach ($wishlists as $wishlist) {
            if ($this->wishlistNameChecker->check($wishlist->getName(), $value)) {
                return true;
            }
        }

        return false;
    }
}
