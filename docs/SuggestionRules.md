# Suggestion Rules

## Scope
Suggestions are generated based on aggregated reporting data and trend analysis.
They are computed per account and integrated into reporting responses.
They are not currently persisted and are generated on demand.

## Data Sources
- Aggregated metrics (averages, counts)
- Trend data (bucketed time-series analysis)
- Central metric definitions from `HealthMetricRegistry`

## Metric Behavior
Metric-specific behavior is defined centrally in the `HealthMetricRegistry`.

This includes:
- supported metric keys
- labels
- type (`number` vs `string`)
- unit
- threshold value
- trend margin
- whether threshold-based suggestions are enabled
- whether trend-based suggestions are enabled

Only metrics marked as numeric participate in threshold and trend rules.
Non-numeric metrics may still generate low-sample-size suggestions, but do not generate threshold or trend-based suggestions.

## Suggestion Output Format

Each suggestion follows this structure:

- `type`: string identifier
- `metric`: metric key or `null` for account-level suggestions
- `severity`: `low | medium | high`
- `title`: short summary of the suggestion
- `message`: user-facing explanation
- `context`: supporting structured data

## Rule Definitions

### Rule: No Data Available

**Condition**
- No health entries exist for the selected time range

**Output**
- `type`: `no_data`
- `metric`: `null`
- `severity`: `low`
- `title`: `No data available`
- `message`: `Not enough data is available to generate insights.`

---

### Rule: Low Sample Size

**Condition**
- Count of numeric entries for a metric is less than 3

**Output**
- `type`: `insufficient_data`
- `metric`: metric key being evaluated
- `severity`: `low`
- `title`: `More data needed`
- `message`: `More data is needed for reliable insights for this metric.`

---

### Rule: High Metric Value

**Condition**
- Average value for a metric exceeds its configured threshold
- Thresholds are defined per metric in `HealthMetricRegistry`

**Example**
- Heart rate average > configured threshold

**Output**
- `type`: `high_value`
- `metric`: metric key being evaluated
- `severity`: `medium`
- `title`: `Metric is above expected range`
- `message`: `Average value is above the expected range.`

---

### Rule: Negative Trend

**Condition**
- Trend analysis shows the last bucket average is greater than the first bucket average by more than the configured trend margin
- Trend margins are defined per metric in `HealthMetricRegistry`

**Output**
- `type`: `negative_trend`
- `metric`: metric key being evaluated
- `severity`: `medium`
- `title`: `Metric trend is worsening`
- `message`: `Recent trend data suggests this metric may be moving in an unhealthy direction.`

---

### Rule: Positive Trend

**Condition**
- Trend analysis shows the last bucket average is lower than the first bucket average by more than the configured trend margin
- Trend margins are defined per metric in `HealthMetricRegistry`

**Output**
- `type`: `positive_trend`
- `metric`: metric key being evaluated
- `severity`: `low`
- `title`: `Metric trend is improving`
- `message`: `Recent trend data suggests this metric may be improving.`

## Post-processing Rules

After raw suggestions are generated:

- Duplicate suggestions with the same `type` and `metric` are removed
- Suggestions are sorted by severity in descending order:
    - `high`
    - `medium`
    - `low`

## Reporting Integration

Suggestions are currently integrated into the `/api/me/summary` reporting response.

The response includes:
- averages
- counts
- suggestions

This allows the reporting system to return both summary metrics and suggestion outputs together in a single response.

## Unknown Metrics / Compatibility

If a metric is not defined in `HealthMetricRegistry`:

- it may still appear in aggregation results
- generic numeric detection may still apply in some backend services for compatibility
- threshold and trend-specific suggestion rules are only applied when metric behavior is explicitly defined in the registry

## Current Design Constraints

The current implementation is intentionally scoped to the backend suggestion pipeline and reporting integration.

The following are intentionally deferred:
- suggestion persistence/history
- scheduled or event-driven generation
- notification-system handoff
- dashboard/UI display implementation
- user settings for enabling/disabling suggestions

## Summary

The current suggestion system:
- uses reporting and trend services as inputs
- evaluates rule-based conditions
- generates structured suggestions
- filters behavior through a centralized metric registry
- integrates suggestions into reporting output
- supports testable, deterministic backend behavior
