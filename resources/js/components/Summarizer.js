import React, { useState } from 'react';

export default function Summarizer() {
    const [summary, setSummary] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleSubmit = async (e) => {
        e.preventDefault();
        const text = e.target.text.value;
        
        if (!text.trim()) return;

        setLoading(true);
        setError('');
        setSummary('');

        try {
            const response = await fetch('/api/summarize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ text })
            });

            const data = await response.json();
            
            if (data.success) {
                setSummary(data.summary);
            } else {
                setError('Error: ' + data.error);
            }
        } catch (err) {
            setError('An error occurred while processing your request.');
            console.error('Error:', err);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-4xl mx-auto p-6">
            <h1 className="text-2xl font-bold mb-6">Text Summarizer</h1>
            <div className="bg-white rounded-lg shadow p-6">
                <form onSubmit={handleSubmit}>
                    <div className="mb-4">
                        <label className="block text-gray-700 text-sm font-bold mb-2">
                            Enter your text:
                        </label>
                        <textarea
                            name="text"
                            className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            rows="6"
                            placeholder="Enter the text you want to summarize..."
                            required
                        ></textarea>
                    </div>
                    <div className="flex items-center justify-between">
                        <button
                            type="submit"
                            className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                            disabled={loading}
                        >
                            {loading ? 'Generating...' : 'Generate Summary'}
                        </button>
                    </div>
                </form>
                {error && (
                    <div className="mt-4 p-4 bg-red-100 text-red-700 rounded">
                        {error}
                    </div>
                )}
                <div className="mt-6">
                    <h2 className="text-xl font-bold mb-2">Summary:</h2>
                    <div className="bg-gray-50 p-4 rounded">
                        {summary || 'Summary will appear here...'}
                    </div>
                </div>
            </div>
        </div>
    );
}
