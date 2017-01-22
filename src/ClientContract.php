<?php
namespace Htunlogic\Poloniex;


interface ClientContract
{
    /**
     * Get my balances.
     *
     * @return array
     */
    function getBalances();

    /**
     * Returns the trading volume.
     *
     * @return array
     */
    function getVolume();

    /**
     * Returning all pairs with their prices.
     *
     * @return array
     */
    function getTickers();

    /**
     * Buy or sell action.
     *
     * @param string $command
     * @param string $pair
     * @param float  $rate
     * @param float  $amount
     * @param string $type
     * @return array
     */
    function buyOrSell($command, $pair, $rate, $amount, $type = null);

    /**
     * Format the dates into numeric timestamps.
     *
     * @param mixed $start
     * @param mixed $end
     * @return array
     */
    function formatDates($start = null, $end = null);

    /**
     * Make request on public API.
     *
     * @param array $parameters
     * @return array
     */
    function public(array $parameters);

    /**
     * Query the trading Api
     *
     * @param array $parameters
     * @return mixed
     */
    function trading(array $parameters = []);
}