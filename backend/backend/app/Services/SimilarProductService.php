<?php

namespace App\Services;

use App\Models\Product;

class SimilarProductService
{
    public function getSimilarProducts($product, $limit = 4)
    {
        // Get the description of the requested product
        $description = $product->description;

        // Calculate TF-IDF vector for the requested product's description
        $tfidfVector = $this->calculateTFIDF($description);

        // Find similar products based on TF-IDF similarity
        $similarProducts = Product::with(['images', 'displayImage', 'category', 'subcategory', 'user'])
            ->select('*')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->get();

        // Calculate TF-IDF vector for each similar product's description
        $similarProductArray = [];
        foreach ($similarProducts as $similarProduct) {
            $similarity = $this->calculateSimilarity($tfidfVector, $similarProduct->description);
            $similarProduct->similarity = $similarity;
            $similarProductArray[] = $similarProduct;
        }

        // Sort similar products by similarity score in descending order and take the top limit
        usort($similarProductArray, function ($a, $b) {
            return $b->similarity <=> $a->similarity;
        });
        $similarProductArray = array_slice($similarProductArray, 0, $limit);

        return $similarProductArray;
    }

    private function descriptionToTFIDFVector($description)
    {
        // Split the text into individual terms (words)
        $terms = explode(' ', $description);

        // Count the frequency of each term in the text
        $termFrequency = array_count_values($terms);

        // Calculate the total number of terms in the text
        $totalTerms = count($terms);

        // Initialize the TF-IDF vector
        $tfidfVector = [];

        // Calculate TF-IDF for each term
        foreach ($termFrequency as $term => $frequency) {
            // Calculate Term Frequency (TF)
            $tf = $frequency / $totalTerms;

            // Calculate Inverse Document Frequency (IDF)
            // For simplicity, let's assume IDF is constant for all terms
            $idf = 1;

            // Calculate TF-IDF
            $tfidf = $tf * $idf;

            // Store TF-IDF for the term
            $tfidfVector[$term] = $tfidf;
        }

        return $tfidfVector;
    }

    private function calculateTFIDF($text)
    {
        return $this->descriptionToTFIDFVector($text);
    }

    private function calculateSimilarity($tfidfVector1, $tfidfVector2)
    {
        // Convert $tfidfVector2 to an array if it's a string
        if (is_string($tfidfVector2)) {
            $tfidfVector2 = $this->descriptionToTFIDFVector($tfidfVector2);
        }

        // Calculate dot product of the two vectors
        $dotProduct = 0;
        foreach ($tfidfVector1 as $term => $tfidf) {
            if (isset($tfidfVector2[$term])) {
                $dotProduct += $tfidf * $tfidfVector2[$term];
            }
        }

        // Calculate magnitude of the first vector
        $magnitude1 = sqrt(array_sum(array_map(function ($tfidf) {
            return $tfidf * $tfidf;
        }, $tfidfVector1)));

        // Calculate magnitude of the second vector
        $magnitude2 = sqrt(array_sum(array_map(function ($tfidf) {
            return $tfidf * $tfidf;
        }, $tfidfVector2)));

        // Calculate cosine similarity
        if ($magnitude1 > 0 && $magnitude2 > 0) {
            $similarity = $dotProduct / ($magnitude1 * $magnitude2);
        } else {
            $similarity = 0; // Handle division by zero
        }

        return $similarity;
    }
}