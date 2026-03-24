# Suggestion Rules

## Scope
Suggestions are generated based on aggregated reporting data and trends.
They are computed per account and are not persisted (computed on demand).

## Data Sources
- Aggregated metrics (averages, counts)
- Trend data (changes over time)

## Suggestion Output Format

Each suggestion will follow this structure:

- type: string (identifier)
- metric: string (e.g., hr, weight)
- severity: low | medium | high
- message: string
- context: object (supporting data)


## Rule Definitions
## --

### Rule: No Data Available

Condition:
- No health entries exist for the selected time range

Output:
- type: no_data
- severity: low
- message: "Not enough data to generate insights."

### Rule: Low Sample Size

Condition:
- Count of entries for a metric < 3

Output:
- type: insufficient_data
- severity: low
- message: "More data is needed for reliable insights."

### Rule: High Metric Value

Condition:
- Average value exceeds threshold
- Example: hr > 85

Output:
- type: high_value
- severity: medium
- message: "Average value is above expected range."
- context: includes avg and threshold

### Rule: Negative Trend

Condition:
- Metric trend shows consistent increase (bad direction)

Output:
- type: negative_trend
- severity: medium
- message: "Metric trend is worsening over time."

### Rule: Positive Trend

Condition:
- Metric trend shows improvement

Output:
- type: positive_trend
- severity: low
- message: "Metric trend is improving."
