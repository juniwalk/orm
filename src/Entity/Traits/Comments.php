<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\ORM\Entity\Traits;

use JuniWalk\ORM\Entity\Interfaces\Comment;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
		return (bool) $this->comments->count();
	}


	public function getCommentsCount(): int
	{
		return $this->comments->count();
	}


	public function getComments(): array
	{
		return $this->comments->toArray();
	}
}
