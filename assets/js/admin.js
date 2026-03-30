document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) {
        lucide.createIcons();
    }

    var minutesInput = document.getElementById('minutesLeftInput');
    var stateBox = document.getElementById('autoStateBox');
    var caseLabel = document.getElementById('autoCaseLabel');
    var subtitle = document.getElementById('autoSubtitle');
    var status = document.getElementById('autoStatus');
    var demand = document.getElementById('autoDemand');

    function stateByMinutes(minutes) {
        if (minutes <= 1800) {
            return {
                key: 'soon',
                caseLabel: stateBox.dataset.soonLabel,
                subtitle: stateBox.dataset.soonSubtitle,
                status: stateBox.dataset.soonStatus,
                demand: stateBox.dataset.soonDemand
            };
        }

        if (minutes <= 3600) {
            return {
                key: 'current',
                caseLabel: stateBox.dataset.currentLabel,
                subtitle: stateBox.dataset.currentSubtitle,
                status: stateBox.dataset.currentStatus,
                demand: stateBox.dataset.currentDemand
            };
        }

        return {
            key: 'long',
            caseLabel: stateBox.dataset.longLabel,
            subtitle: stateBox.dataset.longSubtitle,
            status: stateBox.dataset.longStatus,
            demand: stateBox.dataset.longDemand
        };
    }

    function refreshAutoState() {
        if (!minutesInput || !stateBox || !caseLabel || !subtitle || !status || !demand) {
            return;
        }

        var minutes = Number(minutesInput.value || 0);
        var nextState = stateByMinutes(minutes);

        caseLabel.textContent = nextState.caseLabel;
        subtitle.textContent = nextState.subtitle;
        status.textContent = nextState.status;
        demand.textContent = nextState.demand;
    }

    if (minutesInput) {
        minutesInput.addEventListener('input', refreshAutoState);
        refreshAutoState();
    }

    document.querySelectorAll('.preset-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!minutesInput) {
                return;
            }

            minutesInput.value = String(button.dataset.minutes || '0');

            var progressInput = document.querySelector('input[name="progress_percent"]');
            if (progressInput && button.dataset.progress) {
                progressInput.value = String(button.dataset.progress);
            }

            refreshAutoState();
            minutesInput.focus();
        });
    });
});