<?php

namespace App\Services\BiometricParsers;

interface BiometricParserInterface
{
    /**
     * Parse the biometric file and return attendance records
     *
     * @param string $filePath Full path to the file
     * @return array Array of attendance records with standardized format
     */
    public function parse(string $filePath): array;

    /**
     * Get the format name
     *
     * @return string
     */
    public function getFormatName(): string;

    /**
     * Get supported file extensions
     *
     * @return array
     */
    public function getSupportedExtensions(): array;

    /**
     * Validate if the file format is correct
     *
     * @param string $filePath
     * @return bool
     */
    public function validate(string $filePath): bool;
}
