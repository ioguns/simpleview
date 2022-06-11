<?php

namespace IOguns\SimpleView;

interface IView
{
    public function setDirectory(string $dir): IView;
    public function setDirectories(array $dirs): IView;
    public function getDirectories(): array;
    public function setData(string $key, $value): IView;
    public function populate(array $data): IView;
    public function setView(string $file): IView;
    public function getCurrentView(): ?string;
    public function render(): string;
    public function getContent(): string;
    public function hasBlock(string $name): bool;
    public function getBlockContent(string $name): string;
    public function startBlock(string $name, ...$callbacks): bool;
    public function endBlock(): void;
    public function partial(string $file, array $data = [], array $dirs = []): string;
}
