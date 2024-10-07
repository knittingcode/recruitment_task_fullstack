import React, { useState, useEffect } from 'react';
import { faRightLeft } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

const Calculator = () => {

    // Get initial data from window.__INITIAL_DATA__
    const initialData = window.__INITIAL_DATA__ || { rates: [], dataFromTheDay: '' };

    // State variables
    const [rates] = useState([...initialData.rates, { code: 'PLN', name: 'Polish Zloty', nbpRate: 1 }]); // Add PLN with rate 1
    const [dataFromTheDay] = useState(initialData.dataFromTheDay); // Date of the exchange rate
    const [amount, setAmount] = useState(''); // Amount to be converted
    const [fromCurrency, setFromCurrency] = useState('EUR'); // Source currency
    const [toCurrency, setToCurrency] = useState('PLN'); // Target currency
    const [convertedAmount, setConvertedAmount] = useState(null); // Converted result

    /**
     * Validate input value - replace comma with dot, allow only numbers, dot and comma
     */
    const handleAmountChange = (e) => {
        let value = e.target.value;

        // Replace comma with dot
        value = value.replace(',', '.');

        // Check if the value matches the number format (allow numbers and a dot)
        if (/^\d*\.?\d*$/.test(value)) {
            setAmount(value);
        }

    };

    /**
     * Convert currencies based on rates
     */
    const handleConversion = () => {
        const fromRate = rates.find(rate => rate.code === fromCurrency)?.nbpRate || 1;
        const toRate = rates.find(rate => rate.code === toCurrency)?.nbpRate || 1;

        if (amount) {
            // First convert the amount to PLN, then to the target currency
            const result = (amount * fromRate) / toRate;
            setConvertedAmount(result.toFixed(2));
        } else {
            setConvertedAmount(null); // Reset result if no amount is entered
        }
    };

    /**
     * Swap currencies between 'fromCurrency' and 'toCurrency'
     */
    const handleSwapCurrencies = () => {
        const tempCurrency = fromCurrency;
        setFromCurrency(toCurrency);
        setToCurrency(tempCurrency);
    };

    // Get exchange rate information between the selected currencies
    const getExchangeRateInfo = () => {
        const fromRate = rates.find(rate => rate.code === fromCurrency)?.nbpRate || 1;
        const toRate = rates.find(rate => rate.code === toCurrency)?.nbpRate || 1;
        const exchangeRate = (fromRate / toRate).toFixed(4);
        const reverseRate = (toRate / fromRate).toFixed(4);

        return (
            <>
                <p className="m-0 p-0">1 {fromCurrency} = {exchangeRate} {toCurrency}</p>
                <p className="m-0 p-0">1 {toCurrency} = {reverseRate} {fromCurrency}</p>
            </>
        );
    };

    // Automatically convert currencies when amount, source currency, or target currency changes
    useEffect(() => {
        handleConversion();
    }, [amount, fromCurrency, toCurrency]);

    return (
        <div className="container mt-4">
            <h2>Currency Calculator</h2>
            <p>Exchange rates as of: <strong>{dataFromTheDay}</strong></p> {/* Display the date of exchange rate */}

            {/* First line: 'I HAVE' (input) + currency */}
            <div className="row">
                <div className="col-3">
                    <label htmlFor="amount">I HAVE</label>
                    <input
                        type="number"
                        id="amount"
                        name="amount"
                        className="form-control"
                        value={amount}
                        onChange={handleAmountChange}
                        placeholder="Enter amount"
                    />
                </div>
                <div className="col-2">
                    <label htmlFor="fromCurrency">&nbsp;</label>
                    <select
                        id="fromCurrency"
                        name="fromCurrency"
                        className="form-control"
                        value={fromCurrency}
                        onChange={(e) => {
                            setFromCurrency(e.target.value);
                            if (e.target.value === toCurrency) {
                                setToCurrency(rates.find(rate => rate.code !== e.target.value).code); // Automatically change target currency if they are the same
                            }
                        }}
                    >
                        {rates
                            .filter(rate => rate.code !== toCurrency) // Hide the currency that is currently selected in 'To'
                            .map(rate => (
                                <option key={rate.code} value={rate.code}>
                                    {rate.code}
                                </option>
                            ))}
                    </select>
                </div>
            </div>

            {/* Second line: 'Swap Currencies' button + Exchange rate info */}
            <div className="row my-4 align-items-center">
                <div className="col-2 text-center">
                    <button className="btn btn-primary" onClick={handleSwapCurrencies}>
                        <FontAwesomeIcon icon={faRightLeft} className="fa-rotate-90" />
                    </button>
                </div>
                <div className="col-3 text-center">
                    {getExchangeRateInfo()}
                </div>
            </div>

            {/* Third line: 'I WANT' (input readonly) + currency */}
            <div className="row">
                <div className="col-3">
                    <label htmlFor="convertedAmount">I WANT</label>
                    <input
                        type="number"
                        id="convertedAmount"
                        name="convertedAmount"
                        className="form-control"
                        value={convertedAmount || ''}
                        readOnly
                    />
                </div>
                <div className="col-2">
                    <label htmlFor="toCurrency">&nbsp;</label>
                    <select
                        id="toCurrency"
                        name="toCurrency"
                        className="form-control"
                        value={toCurrency}
                        onChange={(e) => {
                            setToCurrency(e.target.value);
                            if (e.target.value === fromCurrency) {
                                setFromCurrency(rates.find(rate => rate.code !== e.target.value).code); // Automatically change source currency if they are the same
                            }
                        }}
                    >
                        {rates
                            .filter(rate => rate.code !== fromCurrency) // Hide the currency that is currently selected in 'From'
                            .map(rate => (
                                <option key={rate.code} value={rate.code}>
                                    {rate.code}
                                </option>
                            ))}
                    </select>
                </div>
            </div>

        </div>
    );
};

export default Calculator;