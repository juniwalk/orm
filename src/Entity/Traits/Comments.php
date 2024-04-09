<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use JuniWalk\ORM\Entity\Interfaces\Comment;

trait Comments
{
	#[ORM\ManyToMany(targetEntity: Comment::class, indexBy: 'id', orphanRemoval: true, fetch: 'EXTRA_LAZY')]
	#[ORM\OrderBy(['created' => 'DESC'])]
	protected Collection $comments;


	public function addComment(Comment $comment): void
	{
		$this->comments->add($comment);
	}


	public function removeComment(int $commentId): ?Comment
	{
		return $this->comments->remove($commentId);
	}


	public function hasComments(): bool
	{
		return (bool) $this->getCommentsCount();
	}


	public function getCommentsCount(): int
	{
		try {
			return $this->comments->count();

		} catch (EntityNotFoundException) {
		}

		return 0;
	}


	public function getComments(): array
	{
		return $this->comments->toArray();
	}
}
