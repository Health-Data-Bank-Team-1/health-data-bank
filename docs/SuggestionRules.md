# Suggestion Rules

## Scope
Suggestions are generated based on aggregated reporting data and trends.
They are computed per account and are not persisted (computed on demand).

## Metric Behavior

Metric-specific behavior (e.g., numeric vs non-numeric, thresholds, and trend margins) is defined centrally in the HealthMetricRegistry.

- Only metrics marked as numeric will participate in threshold and trend rules.
- Threshold and trend rules are only applied when enabled for a given metric.
- Non-numeric metrics may still produce "insufficient_data" suggestions but will not generate threshold or trend-based suggestions.

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
- Threshold values are defined per metric in the HealthMetricRegistry.
  Example: Heart rate may have a threshold of 85 bpm.

Output:
- type: high_value
- severity: medium
- message: "Average value is above expected range."
- context: includes avg and threshold

### Rule: Negative Trend

Condition:
- Metric trend shows consistent increase (bad direction)
- Latest average exceeds earliest average by a configured margin
- Trend margins are defined per metric in the HealthMetricRegistry.

Output:
- type: negative_trend
- severity: medium
- message: "Metric trend is worsening over time."

### Rule: Positive Trend

Condition:
- Metric trend shows improvement
- Latest average exceeds earliest average by a configured margin
- Trend margins are defined per metric in the HealthMetricRegistry.

Output:
- type: positive_trend
- severity: low
- message: "Metric trend is improving."

## Post-processing

- Duplicate suggestions (same type and metric) are removed.
- Suggestions are sorted by severity (high → medium → low).

## Unknown Metrics

If a metric is not defined in the HealthMetricRegistry:
- It may still be included in aggregation results.
- Numeric detection falls back to generic numeric checks.
- Threshold and trend rules are not applied unless explicitly defined.
