<?php

interface UserInterface {
    public function getUserId(): string;
    public function getFullName(): string;
    public function getEmail(): string;
    public function getRole(): string;
    public function login(): bool|array;
    public function logout(): void;
}
