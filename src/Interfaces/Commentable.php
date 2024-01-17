<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Interfaces;

interface Commentable
{
	public function addComment(Comment $comment): void;
	public function removeComment(int $commentId): ?Comment;
	public function hasComments(): bool;
	public function getComments(): array;
}
