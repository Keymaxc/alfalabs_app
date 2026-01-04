#!/usr/bin/env python3
import json
import sys


def main():
    if len(sys.argv) < 4:
        print(json.dumps({"error": "invalid_args"}))
        return

    try:
        import numpy  # noqa: F401
        from statsmodels.tsa.statespace.sarimax import SARIMAX
    except Exception as exc:  # pragma: no cover - defensive for missing deps
        print(json.dumps({"error": f"missing_dependencies:{exc}"}))
        return

    try:
        series = json.loads(sys.argv[1])
        periods = int(sys.argv[2])
        seasonal_period = int(sys.argv[3])
    except Exception as exc:
        print(json.dumps({"error": f"parse_error:{exc}"}))
        return

    if periods <= 0:
        print(json.dumps({"predictions": []}))
        return

    if not series or len(series) < max(3, seasonal_period * 2):
        avg = sum(series) / len(series) if series else 0
        print(json.dumps({"predictions": [avg for _ in range(periods)], "fallback": True}))
        return

    try:
        model = SARIMAX(
            series,
            order=(1, 1, 1),
            seasonal_order=(1, 1, 1, seasonal_period),
            enforce_stationarity=False,
            enforce_invertibility=False,
        )
        result = model.fit(disp=False)
        forecast = result.forecast(steps=periods)
        predictions = [max(0.0, float(x)) for x in forecast]

        print(
            json.dumps(
                {
                    "predictions": predictions,
                    "aic": getattr(result, "aic", None),
                    "bic": getattr(result, "bic", None),
                }
            )
        )
    except Exception as exc:
        avg = sum(series) / len(series)
        print(json.dumps({"predictions": [avg for _ in range(periods)], "error": str(exc), "fallback": True}))


if __name__ == "__main__":
    main()
