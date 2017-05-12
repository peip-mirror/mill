<?php
namespace Mill\Examples\Showtimes\Representations;

/**
 * Data representation for a specific movie.
 *
 * @api-label Movie
 */
class Movie extends Representation
{
    protected $movie;

    public function create()
    {
        return [
            /**
             * @api-data uri (uri) - Movie URI
             */
            'uri' => $this->movie->uri,

            /**
             * @api-data id (number) - Unique ID
             */
            'id' => $this->movie->id,

            /**
             * @api-data name (string) - Name
             */
            'name' => $this->movie->name,

            /**
             * @api-data description (string) - Description
             */
            'description' => $this->movie->description,

            /**
             * @api-data runtime (string) - Runtime
             */
            'runtime' => $this->movie->runtime,

            /**
             * @api-data content_rating (enum) - MPAA rating
             *      + Members
             *          - `G`
             *          - `PG`
             *          - `PG-13`
             *          - `R`
             *          - `NC-17`
             *          - `X`
             *          - `NR`
             *          - `UR`
             */
            'rating' => $this->movie->rating,

            /**
             * @api-data genres (array) - Genres
             */
            'genres' => $this->movie->getGenres(),

            /**
             * @api-data director (\Mill\Examples\Showtimes\Representations\Person) - Director
             */
            'director' => $this->movie->director,

            /**
             * @api-data cast (array<\Mill\Examples\Showtimes\Representations\Person>) - Cast
             */
            'cast' => $this->movie->getCast(),

            /**
             * @api-data kid_friendly (boolean) - Kid friendly?
             */
            'kid_friendly' => $this->movie->is_kid_friendly,

            /**
             * @api-data theaters (array<\Mill\Examples\Showtimes\Representations\Theater>) - Theaters the movie is
             *      currently showing in
             */
            'theaters' => $this->movie->getTheaters(),

            /**
             * @api-data showtimes (array) - Non-theater specific showtimes
             */
            'showtimes' => $this->getShowtimes(),

            /**
             * @api-data external_urls (object) - External URLs
             * @api-version >=1.1
             * @api-see self::getExternalUrls external_urls
             */
            'external_urls' => $this->getExternalUrls(),

            /**
             * @api-data rotten_tomatoes_score (number) - Rotten Tomatoes score
             */
            'rotten_tomatoes_score' => $this->rotten_tomatoes_score
        ];
    }

    /**
     * @return array
     */
    private function getExternalUrls()
    {
        /**
         * @api-data imdb (string) - IMDB URL
         * @api-data trailer (string) - Trailer URL
         * @api-data tickets (string, BUY_TICKETS) - Tickets URL
         */
        return [
            'imdb' => $this->movie->imdb,
            'trailer' => $this->movie->trailer,
            'tickets' => $this->movie->tickets_url
        ];
    }
}
