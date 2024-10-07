import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useParams, useHistory } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faArrowUp, faArrowDown } from '@fortawesome/free-solid-svg-icons';

/**
 * ExchangeRatesTable component - displays exchange rates for selected date and compares them to today's rates.
 * Allows users to select a date and see rate changes with indicators (arrows).
 */
const ExchangeRatesTable = () => {

    // Get the 'date' parameter from the URL
    const { date: urlDate } = useParams();
    const history = useHistory(); // Allows navigation
    const today = new Date().toISOString().split('T')[0]; // Get today's date in 'YYYY-MM-DD' format

    // State variables
    const [selectedDate, setSelectedDate] = useState(urlDate || today); // Selected date state
    const [rates, setRates] = useState([]); // Exchange rates for selected date
    const [todayRates, setTodayRates] = useState([]); // Exchange rates for today
    const [loading, setLoading] = useState(true); // Loading state
    const [error, setError] = useState(null); // Error state

    /**
     * useEffect hook - runs on component mount and when selectedDate or urlDate changes.
     * Fetches exchange rate data based on the selected date.
     */
    useEffect(() => {
        if (urlDate && urlDate !== selectedDate) setSelectedDate(urlDate);
        fetchData(selectedDate);
    }, [urlDate, selectedDate]);

    /**
     * Fetches exchange rate data from the API for the given date.
     * @param {string} date - The date to fetch exchange rates for.
     */
    const fetchData = async (date) => {
        setLoading(true); // Set loading state to true
        setError(null); // Clear any previous errors
        try {
            const response = await axios.get(`/api/exchange-rates?date=${date}`);
            setRates(response.data.rates); // Set rates for the selected date
            setTodayRates(response.data.todayRates); // Set rates for today
        } catch (error) {
            // Set error message if fetching fails
            setError('Failed to fetch exchange rates. Please try again later.');
        }
        setLoading(false); // Set loading state to false
    };

    /**
     * Handles the date change event when the user selects a new date.
     * Updates the URL with the new date and sets the selectedDate state.
     * @param {object} e - The event object from the input change event.
     */
    const handleDateChange = (e) => {
        const newDate = e.target.value; // Get the new date from the input
        setSelectedDate(newDate); // Update the selected date state
        history.push(`/exchange-rates/${newDate}`); // Update URL with the new date
    };

    /**
     * Renders an arrow icon indicating whether the exchange rate has increased or decreased.
     * Returns null if the selected date is today or if there is no change.
     * @param {number} currentRate - The exchange rate for the selected date.
     * @param {number} todayRate - The exchange rate for today.
     * @returns {JSX.Element|null} - FontAwesomeIcon indicating rate change or null if no change.
     */
    const renderChangeArrow = (currentRate, todayRate) => {

        // Do not show arrows if the selected date is today
        if (selectedDate === today) return null;

        const change = currentRate - todayRate; // Calculate the change in rate
        if (change < 0) return <FontAwesomeIcon icon={faArrowDown} className="text-danger" />; // Red arrow down for decrease
        if (change > 0) return <FontAwesomeIcon icon={faArrowUp} className="text-success" />; // Green arrow up for increase
        return null;

    };

    return (
        <div className="container mt-4">
            <h2>Exchange Rates</h2>
            <div className="form-group">
                <label htmlFor="date">Select Date:</label>
                <input
                    type="date"
                    id="date"
                    name="date"
                    className="form-control"
                    value={selectedDate}
                    onChange={handleDateChange}
                    min="2023-01-01"
                    max={today}
                />
            </div>
            {loading ? (
                <p>Loading...</p>
            ) : error ? (
                <p className="text-danger">{error}</p>
            ) : (
                <table className="table table-bordered mt-3">
                    <thead>
                    <tr>
                        <th>Currency</th>
                        <th>Code</th>
                        <th>NBP Rate (Selected Date)</th>
                        <th>Buy Rate (Selected Date)</th>
                        <th>Sell Rate (Selected Date)</th>
                        <th>NBP Rate (Today)</th>
                        <th>Buy Rate (Today)</th>
                        <th>Sell Rate (Today)</th>
                    </tr>
                    </thead>
                    <tbody>
                    {rates.map((rate, index) => (
                        <tr key={rate.code}>
                            <td>{rate.name}</td>
                            <td>{rate.code}</td>
                            <td>{rate.nbpRate}</td>
                            <td>{rate.buyRate ? rate.buyRate : '-'}</td>
                            <td>{rate.sellRate}</td>
                            <td>
                                {renderChangeArrow(rate.nbpRate, todayRates[index]?.nbpRate)}
                                {todayRates[index]?.nbpRate}
                            </td>
                            <td>
                                {rate.buyRate && renderChangeArrow(rate.buyRate, todayRates[index]?.buyRate)}
                                {todayRates[index]?.buyRate ? todayRates[index]?.buyRate : '-'}
                            </td>
                            <td>
                                {renderChangeArrow(rate.sellRate, todayRates[index]?.sellRate)}
                                {todayRates[index]?.sellRate}
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            )}
        </div>
    );
};

export default ExchangeRatesTable;